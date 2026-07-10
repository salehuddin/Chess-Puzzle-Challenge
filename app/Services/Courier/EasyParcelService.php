<?php

namespace App\Services\Courier;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class EasyParcelService
{
    protected string $apiKey;

    protected bool $sandbox;

    public function __construct(string $apiKey, bool $sandbox = false)
    {
        $this->apiKey = $apiKey;
        $this->sandbox = $sandbox;
    }

    public function baseUrl(): string
    {
        return $this->sandbox
            ? 'https://demo.connect.easyparcel.my/?ac='
            : 'https://connect.easyparcel.my/?ac=';
    }

    /**
     * Check account credit balance.
     *
     * @return array<string, mixed>
     */
    public function checkCredit(): array
    {
        $response = $this->request('EPCheckCreditBulk', []);

        return $this->decodeResponse($response);
    }

    /**
     * Get shipping rates.
     *
     * @param  array<string, mixed>  $from
     * @param  array<string, mixed>  $to
     * @param  array<string, mixed>  $package
     * @return array<string, mixed>
     */
    public function getRates(array $from, array $to, array $package): array
    {
        $payload = [
            'pick_code' => $from['postcode'],
            'pick_state' => $this->mapState($from['state']),
            'pick_country' => $from['country'] ?? 'MY',
            'send_code' => $to['postcode'],
            'send_state' => $this->mapState($to['state']),
            'send_country' => $to['country'] ?? 'MY',
            'weight' => $package['weight'] ?? 0.5,
            'width' => $package['width'] ?? 0,
            'length' => $package['length'] ?? 0,
            'height' => $package['height'] ?? 0,
            'date_coll' => $package['collect_date'] ?? now()->toDateString(),
        ];

        $response = $this->request('EPRateCheckingBulk', $payload);

        return $this->decodeResponse($response);
    }

    /**
     * Find the cheapest pickup-capable service.
     *
     * @param  array<int, array<string, mixed>>  $rates
     */
    public function selectPickupService(array $rates): ?array
    {
        $pickupRates = array_filter($rates, function (array $rate): bool {
            $detail = strtolower($rate['service_detail'] ?? '');

            return str_contains($detail, 'pickup');
        });

        if (empty($pickupRates)) {
            return null;
        }

        usort($pickupRates, fn (array $a, array $b): int => (float) ($a['price'] ?? 0) <=> (float) ($b['price'] ?? 0));

        return $pickupRates[0];
    }

    /**
     * Submit a shipment order.
     *
     * @param  array<string, mixed>  $from
     * @param  array<string, mixed>  $to
     * @param  array<string, mixed>  $package
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function submitOrder(array $from, array $to, array $package, array $options = []): array
    {
        $payload = [
            'weight' => $package['weight'] ?? 0.5,
            'width' => $package['width'] ?? 0,
            'length' => $package['length'] ?? 0,
            'height' => $package['height'] ?? 0,
            'content' => $package['content'] ?? 'Chess medal',
            'value' => $package['value'] ?? 0,
            'service_id' => $options['service_id'] ?? '',
            'pick_point' => $options['pick_point'] ?? '',
            'pick_name' => $from['name'] ?? '',
            'pick_company' => $from['company'] ?? '',
            'pick_contact' => $this->cleanPhone($from['phone'] ?? ''),
            'pick_mobile' => $this->cleanPhone($from['phone'] ?? ''),
            'pick_addr1' => $from['address_line1'] ?? '',
            'pick_addr2' => $from['address_line2'] ?? '',
            'pick_addr3' => '',
            'pick_addr4' => '',
            'pick_city' => $from['city'] ?? '',
            'pick_state' => $this->mapState($from['state'] ?? ''),
            'pick_code' => $from['postcode'] ?? '',
            'pick_country' => $from['country'] ?? 'MY',
            'send_point' => $options['send_point'] ?? '',
            'send_name' => $to['name'] ?? '',
            'send_company' => $to['company'] ?? '',
            'send_contact' => $this->cleanPhone($to['phone'] ?? ''),
            'send_mobile' => $this->cleanPhone($to['phone'] ?? ''),
            'send_addr1' => $to['address_line1'] ?? '',
            'send_addr2' => $to['address_line2'] ?? '',
            'send_addr3' => '',
            'send_addr4' => '',
            'send_city' => $to['city'] ?? '',
            'send_state' => $this->mapState($to['state'] ?? ''),
            'send_code' => $to['postcode'] ?? '',
            'send_country' => $to['country'] ?? 'MY',
            'collect_date' => $options['collect_date'] ?? now()->toDateString(),
            'sms' => $options['sms'] ?? false,
            'send_email' => $to['email'] ?? '',
            'REQ_ID' => $options['reference'] ?? '',
            'reference' => $options['reference'] ?? '',
        ];

        $response = $this->request('EPSubmitOrderBulk', $payload);

        return $this->decodeResponse($response);
    }

    /**
     * Pay for a placed order.
     *
     * @return array<string, mixed>
     */
    public function payOrder(string $orderNumber): array
    {
        $response = $this->request('EPPayOrderBulk', ['order_no' => $orderNumber]);

        return $this->decodeResponse($response);
    }

    /**
     * Get order status.
     *
     * @return array<string, mixed>
     */
    public function orderStatus(string $orderNumber): array
    {
        $response = $this->request('EPOrderStatusBulk', ['order_no' => $orderNumber]);

        return $this->decodeResponse($response);
    }

    /**
     * Get parcel status.
     *
     * @return array<string, mixed>
     */
    public function parcelStatus(string $orderNumber): array
    {
        $response = $this->request('EPParcelStatusBulk', ['order_no' => $orderNumber]);

        return $this->decodeResponse($response);
    }

    /**
     * Track a parcel by parcel number.
     *
     * @return array<string, mixed>
     */
    public function trackParcel(string $parcelNumber): array
    {
        $response = $this->request('EPTrackingBulk', ['parcelno' => $parcelNumber]);

        return $this->decodeResponse($response);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function request(string $action, array $params): Response
    {
        $url = $this->baseUrl().$action;

        $payload = [
            'api' => $this->apiKey,
            'bulk' => [$params],
        ];

        return Http::timeout(30)
            ->connectTimeout(10)
            ->asForm()
            ->post($url, $payload);
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeResponse(Response $response): array
    {
        if (! $response->successful()) {
            return [
                'api_status' => 'Error',
                'error_code' => (string) $response->status(),
                'error_remark' => 'HTTP error: '.$response->body(),
                'result' => [],
            ];
        }

        $body = $response->json();

        return is_array($body) ? $body : [
            'api_status' => 'Error',
            'error_code' => '500',
            'error_remark' => 'Invalid JSON response',
            'result' => [],
        ];
    }

    /**
     * Map a Malaysian state name to EasyParcel state code.
     */
    protected function mapState(string $state): string
    {
        $state = trim(strtolower($state));

        $map = [
            'johor' => 'jhr',
            'kedah' => 'kdh',
            'kelantan' => 'ktn',
            'kuala lumpur' => 'kul',
            'labuan' => 'lbn',
            'melaka' => 'mlk',
            'negeri sembilan' => 'nsn',
            'pahang' => 'phg',
            'penang' => 'png',
            'pulau pinang' => 'png',
            'perak' => 'prk',
            'perlis' => 'pls',
            'putrajaya' => 'pjy',
            'sabah' => 'sbh',
            'sarawak' => 'swk',
            'selangor' => 'sgr',
            'terengganu' => 'trg',
        ];

        return $map[$state] ?? $state;
    }

    /**
     * Strip non-numeric characters from a phone number.
     */
    protected function cleanPhone(?string $phone): string
    {
        if (blank($phone)) {
            return '';
        }

        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        return $cleaned ?? '';
    }
}
