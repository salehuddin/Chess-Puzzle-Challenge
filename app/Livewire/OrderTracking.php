<?php

namespace App\Livewire;

use App\Models\Enrollment;
use Livewire\Attributes\Layout;
use Livewire\Component;

class OrderTracking extends Component
{
    public Enrollment $enrollment;

    public function mount(Enrollment $enrollment)
    {
        if (auth()->id() !== $enrollment->user_id) {
            abort(403);
        }

        $this->enrollment = $enrollment->load([
            'challenge',
            'fulfillment',
            'orderItem.order',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildTrackingData(): array
    {
        $fulfillment = $this->enrollment->fulfillment;
        $order = $this->enrollment->orderItem?->order;

        $status = match (true) {
            in_array($fulfillment?->status, ['shipped', 'delivered'], true) => 'shipped',
            $fulfillment?->status === 'ready_to_ship' || $this->enrollment->status === 'completed' => 'completed',
            $this->enrollment->status === 'active' => 'in_progress',
            $order?->status === 'pending' => 'pending',
            default => 'pending',
        };

        return [
            'order_id' => $order?->id ?? $this->enrollment->id,
            'challenge_name' => $this->enrollment->challenge?->name,
            'status' => $status,
            'created_at' => $order?->created_at ?? $this->enrollment->created_at,
            'completed_at' => $this->enrollment->completed_at,
            'shipped_at' => $fulfillment?->shipped_at,
            'courier' => $fulfillment?->courier,
            'tracking_number' => $fulfillment?->tracking_number,
            'tracking_url' => $fulfillment?->tracking_url,
            'address_snapshot' => $fulfillment?->address_snapshot,
            'enrollment_id' => $this->enrollment->id,
        ];
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.order-tracking', [
            'tracking' => $this->buildTrackingData(),
        ]);
    }
}
