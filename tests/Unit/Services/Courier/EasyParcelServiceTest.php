<?php

namespace Tests\Unit\Services\Courier;

use App\Services\Courier\EasyParcelService;
use PHPUnit\Framework\TestCase;

class EasyParcelServiceTest extends TestCase
{
    public function test_state_names_are_mapped_to_easy_parcel_codes(): void
    {
        $service = new EasyParcelService('test-key');

        $reflection = new \ReflectionMethod($service, 'mapState');

        $this->assertSame('kul', $reflection->invoke($service, 'Kuala Lumpur'));
        $this->assertSame('sgr', $reflection->invoke($service, 'Selangor'));
        $this->assertSame('png', $reflection->invoke($service, 'Penang'));
        $this->assertSame('jhr', $reflection->invoke($service, 'Johor'));
    }

    public function test_pickup_service_is_selected_by_cheapest_price(): void
    {
        $service = new EasyParcelService('test-key');

        $rates = [
            ['service_detail' => 'dropoff', 'price' => '5.00', 'service_id' => 'EP-CS1'],
            ['service_detail' => 'pickup', 'price' => '7.50', 'service_id' => 'EP-CS2'],
            ['service_detail' => 'dropoff/pickup', 'price' => '6.00', 'service_id' => 'EP-CS3'],
        ];

        $selected = $service->selectPickupService($rates);

        $this->assertSame('EP-CS3', $selected['service_id']);
    }

    public function test_pickup_service_returns_null_when_no_pickup_available(): void
    {
        $service = new EasyParcelService('test-key');

        $rates = [
            ['service_detail' => 'dropoff', 'price' => '5.00', 'service_id' => 'EP-CS1'],
        ];

        $this->assertNull($service->selectPickupService($rates));
    }

    public function test_phone_numbers_are_cleaned_to_digits_only(): void
    {
        $service = new EasyParcelService('test-key');

        $reflection = new \ReflectionMethod($service, 'cleanPhone');

        $this->assertSame('60123456789', $reflection->invoke($service, '+60 12-345 6789'));
        $this->assertSame('0123456789', $reflection->invoke($service, '012-345 6789'));
        $this->assertSame('', $reflection->invoke($service, null));
    }
}
