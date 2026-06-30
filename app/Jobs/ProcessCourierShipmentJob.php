<?php

namespace App\Jobs;

use App\Models\Fulfillment;
use App\Services\Courier\EasyParcelService;
use App\Services\MedalInventoryService;
use App\Services\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessCourierShipmentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $fulfillmentId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fulfillment = Fulfillment::with(['enrollment.user', 'enrollment.challenge'])->find($this->fulfillmentId);

        if (! $fulfillment) {
            Log::warning('ProcessCourierShipmentJob: Fulfillment not found.', [
                'fulfillment_id' => $this->fulfillmentId,
            ]);

            return;
        }

        $courier = Settings::courier();
        $provider = $courier['provider'] ?? 'none';
        $hq = Settings::defaultHq();

        if ($provider === 'none') {
            Log::info('ProcessCourierShipmentJob: Courier provider is set to none. Manual fulfillment required.', [
                'fulfillment_id' => $fulfillment->id,
            ]);

            $fulfillment->update([
                'courier' => 'manual',
                'status' => 'ready_to_ship',
            ]);

            return;
        }

        if (! $hq) {
            Log::warning('ProcessCourierShipmentJob: No default HQ address configured.', [
                'fulfillment_id' => $fulfillment->id,
            ]);

            return;
        }

        try {
            $result = match ($provider) {
                'easyparcel' => $this->dispatchEasyParcel($fulfillment, $courier, $hq),
                'dhl' => $this->dispatchDhl($fulfillment, $courier, $hq),
                default => throw new \InvalidArgumentException("Unsupported courier provider: {$provider}"),
            };

            DB::transaction(function () use ($fulfillment, $provider, $result): void {
                $fulfillment->update([
                    'courier' => $provider,
                    'tracking_number' => $result['tracking_number'] ?? null,
                    'tracking_url' => $result['tracking_url'] ?? null,
                    'shipped_at' => now(),
                    'status' => 'shipped',
                ]);

                $challenge = $fulfillment->enrollment?->challenge;

                if ($challenge) {
                    app(MedalInventoryService::class)
                        ->decrementForShipment($challenge, $fulfillment);
                }
            });

            Log::info('ProcessCourierShipmentJob: Shipment dispatched.', [
                'fulfillment_id' => $fulfillment->id,
                'provider' => $provider,
                'tracking_number' => $result['tracking_number'] ?? null,
                'order_number' => $result['order_number'] ?? null,
            ]);
        } catch (Throwable $e) {
            Log::error('ProcessCourierShipmentJob: Failed to dispatch shipment.', [
                'fulfillment_id' => $fulfillment->id,
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Dispatch a shipment via EasyParcel.
     *
     * @return array<string, mixed>
     */
    protected function dispatchEasyParcel(Fulfillment $fulfillment, array $courier, array $hq): array
    {
        $apiKey = $courier['api_key'] ?? '';

        if (blank($apiKey)) {
            throw new \RuntimeException('EasyParcel API key is not configured.');
        }

        $service = new EasyParcelService($apiKey, (bool) ($courier['sandbox_mode'] ?? false));

        $user = $fulfillment->enrollment?->user;
        $challenge = $fulfillment->enrollment?->challenge;
        $fallbackPhone = $courier['fallback_phone'] ?? '';

        $toAddress = $this->normalizeAddress(
            $fulfillment->address_snapshot ?? ($user ? $user->addressSnapshot() : [])
        );
        $toAddress['name'] = $user?->name ?? 'Recipient';
        $toAddress['email'] = $user?->email ?? '';
        $toAddress['phone'] = filled($toAddress['phone']) ? $toAddress['phone'] : $fallbackPhone;

        $fromAddress = $this->normalizeAddress($hq);
        $fromAddress['name'] = $hq['contact_name'] ?? ($hq['name'] ?? 'Sender');
        $fromAddress['email'] = $hq['email'] ?? '';
        $fromAddress['phone'] = filled($hq['phone'] ?? '') ? $hq['phone'] : $fallbackPhone;

        if (blank($toAddress['phone'])) {
            Log::warning('ProcessCourierShipmentJob: Recipient phone is missing. EasyParcel may reject the order.', [
                'fulfillment_id' => $fulfillment->id,
            ]);
        }

        $package = [
            'weight' => $challenge?->medal_weight ?? ($courier['parcel_weight'] ?? 0.5),
            'length' => $challenge?->medal_length ?? ($courier['parcel_length'] ?? 10),
            'width' => $challenge?->medal_width ?? ($courier['parcel_width'] ?? 10),
            'height' => $challenge?->medal_height ?? ($courier['parcel_height'] ?? 5),
            'content' => $courier['parcel_content'] ?? 'Chess medal',
            'value' => $courier['parcel_value'] ?? 0,
            'collect_date' => $courier['collect_date'] ?? now()->toDateString(),
        ];

        $serviceId = $courier['easyparcel_service_id'] ?? '';

        if (blank($serviceId)) {
            $ratesResponse = $service->getRates($fromAddress, $toAddress, $package);
            $rates = $ratesResponse['result'][0]['rates'] ?? [];
            $selectedRate = $service->selectPickupService($rates);

            if (! $selectedRate) {
                throw new \RuntimeException('No pickup service available for this route.');
            }

            $serviceId = $selectedRate['service_id'];
        }

        $reference = 'FUL-'.$fulfillment->id;

        $submitResponse = $service->submitOrder($fromAddress, $toAddress, $package, [
            'service_id' => $serviceId,
            'reference' => $reference,
            'sms' => false,
        ]);

        $submitResult = $submitResponse['result'][0] ?? null;

        if (($submitResponse['api_status'] ?? '') !== 'Success' || ! $submitResult || ($submitResult['status'] ?? '') !== 'Success') {
            throw new \RuntimeException(
                'EasyParcel submit order failed: '.
                ($submitResult['remarks'] ?? ($submitResponse['error_remark'] ?? 'Unknown error'))
            );
        }

        $orderNumber = $submitResult['order_number'];
        $parcelNumber = $submitResult['parcel_number'];

        $trackingNumber = $parcelNumber;
        $trackingUrl = 'https://www.easyparcel.com/en-my/tracking/?no='.$parcelNumber;

        if ((bool) ($courier['auto_pay'] ?? false)) {
            $payResponse = $service->payOrder($orderNumber);
            $payResult = $payResponse['result'][0] ?? null;

            if ($payResult && ($payResult['messagenow'] ?? '') === 'Fully Paid') {
                $parcel = $payResult['parcel'][0] ?? [];
                $trackingNumber = $parcel['awb'] ?? $parcelNumber;
                $trackingUrl = $parcel['tracking_url'] ?? $trackingUrl;
            } else {
                Log::warning('ProcessCourierShipmentJob: EasyParcel order not paid.', [
                    'fulfillment_id' => $fulfillment->id,
                    'order_number' => $orderNumber,
                    'message' => $payResult['messagenow'] ?? ($payResponse['error_remark'] ?? 'Unknown'),
                ]);
            }
        }

        return [
            'tracking_number' => $trackingNumber,
            'tracking_url' => $trackingUrl,
            'order_number' => $orderNumber,
            'parcel_number' => $parcelNumber,
            'service_id' => $serviceId,
        ];
    }

    /**
     * Dispatch a shipment via DHL (stub).
     *
     * @return array<string, mixed>
     */
    protected function dispatchDhl(Fulfillment $fulfillment, array $courier, array $hq): array
    {
        Log::info('ProcessCourierShipmentJob: DHL dispatch stub executed.', [
            'fulfillment_id' => $fulfillment->id,
            'sandbox' => $courier['sandbox_mode'] ?? false,
            'hq' => $hq['name'] ?? null,
        ]);

        $trackingNumber = 'DHL'.strtoupper(uniqid());

        return [
            'tracking_number' => $trackingNumber,
            'tracking_url' => 'https://www.dhl.com/us-en/home/tracking/tracking-express.html?submit=1&tracking-id='.$trackingNumber,
        ];
    }

    /**
     * Normalize an address array to use consistent keys.
     *
     * @param  array<string, mixed>  $address
     * @return array<string, mixed>
     */
    protected function normalizeAddress(array $address): array
    {
        return [
            'name' => $address['name'] ?? ($address['contact_name'] ?? ''),
            'company' => $address['company'] ?? '',
            'phone' => $address['phone'] ?? ($address['mobile'] ?? ($address['contact'] ?? '')),
            'email' => $address['email'] ?? '',
            'address_line1' => $address['address_line1'] ?? ($address['addr1'] ?? ''),
            'address_line2' => $address['address_line2'] ?? ($address['addr2'] ?? ''),
            'city' => $address['city'] ?? '',
            'state' => $address['state'] ?? '',
            'postcode' => $address['postcode'] ?? ($address['postal_code'] ?? ($address['zip'] ?? '')),
            'country' => $address['country'] ?? 'MY',
        ];
    }
}
