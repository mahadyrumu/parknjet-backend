<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;
use Tests\TestCase;

use App\Services\Pricing\PricingService;
use DateTime;
use Illuminate\Support\Facades\Log;

class PricingTest extends TestCase
{

    public function test_get_pricing(): void
    {
        $dropOff = (new DateTime())->modify('+' . rand(1, 20) . ' days')->setTime(rand(1, 23), 0);
        $pickUp = (clone $dropOff)->modify('+' . rand(5, 11) . ' days')->setTime(rand(1, 23), 0);

        $dropOff = $dropOff->format('Y-m-d h:i A');
        $pickUp = $pickUp->format('Y-m-d h:i A');

        foreach (LotType as $key1 => $lot) {
            foreach (vehicle_length as $key2 => $vehicle) {
                if ($key2 != "image") {
                    (new PricingService())->getPricing($lot, $dropOff, $pickUp, "VALET", $key2, null, 0);
                    if ($lot != "LOT_1") {
                        (new PricingService())->getPricing($lot, $dropOff, $pickUp, "SELF", $key2, null, 0);
                    }
                }
            }
        }

        // $getPricing = (new PricingService())->getPricing("LOT_2", "2025-05-05 12:00 PM", "2025-05-15 02:00 PM", "VALET", "STANDARD", null, 0);
        $this->assertTrue(true);
    }
}
