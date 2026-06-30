<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Models\Fulfillment;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MedalRequest extends Component
{
    public Enrollment $enrollment;

    public $challenge;

    public ?Fulfillment $fulfillment = null;

    public bool $alreadyRequested = false;

    public string $addressLine1 = '';

    public string $addressLine2 = '';

    public string $city = '';

    public string $state = '';

    public string $postcode = '';

    public string $country = '';

    public bool $saveToProfile = true;

    public function mount(Enrollment $enrollment): void
    {
        if (auth()->id() !== $enrollment->user_id) {
            abort(403);
        }

        if ($enrollment->status !== 'completed') {
            abort(403);
        }

        $this->enrollment = $enrollment;
        $this->challenge = $enrollment->challenge()->withCount('puzzles')->first();
        $this->fulfillment = Fulfillment::where('enrollment_id', $enrollment->id)->first();

        if ($this->fulfillment && $this->fulfillment->status !== 'pending') {
            $this->alreadyRequested = true;
        }

        $this->prefillAddress();
    }

    protected function prefillAddress(): void
    {
        $user = auth()->user();

        $snapshot = $this->fulfillment?->address_snapshot;

        if (is_array($snapshot) && ! empty(array_filter($snapshot))) {
            $this->addressLine1 = (string) ($snapshot['address_line1'] ?? '');
            $this->addressLine2 = (string) ($snapshot['address_line2'] ?? '');
            $this->city = (string) ($snapshot['city'] ?? '');
            $this->state = (string) ($snapshot['state'] ?? '');
            $this->postcode = (string) ($snapshot['postcode'] ?? '');
            $this->country = (string) ($snapshot['country'] ?? '');

            return;
        }

        $this->addressLine1 = (string) ($user->address_line1 ?? '');
        $this->addressLine2 = (string) ($user->address_line2 ?? '');
        $this->city = (string) ($user->city ?? '');
        $this->state = (string) ($user->state ?? '');
        $this->postcode = (string) ($user->postcode ?? '');
        $this->country = (string) ($user->country ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'addressLine1' => ['required', 'string', 'max:255'],
            'addressLine2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
        ];
    }

    public function requestMedal(): void
    {
        $validated = $this->validate();

        if ($this->alreadyRequested) {
            $this->redirect(route('dashboard'), navigate: true);

            return;
        }

        DB::transaction(function () use ($validated) {
            $user = auth()->user();

            if ($this->saveToProfile) {
                $user->address_line1 = $validated['addressLine1'];
                $user->address_line2 = $validated['addressLine2'];
                $user->city = $validated['city'];
                $user->state = $validated['state'];
                $user->postcode = $validated['postcode'];
                $user->country = $validated['country'];
                $user->save();
            }

            $addressSnapshot = [
                'address_line1' => $validated['addressLine1'],
                'address_line2' => $validated['addressLine2'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postcode' => $validated['postcode'],
                'country' => $validated['country'],
            ];

            $fulfillment = Fulfillment::firstOrNew([
                'enrollment_id' => $this->enrollment->id,
            ]);

            $fulfillment->status = 'ready_to_ship';
            $fulfillment->address_snapshot = $addressSnapshot;
            $fulfillment->save();

            $this->fulfillment = $fulfillment->fresh();
            $this->alreadyRequested = true;
        });

        $this->dispatch('medal-requested');

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function requestLater(): void
    {
        $this->redirect(route('dashboard'), navigate: true);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.medal-request');
    }
}
