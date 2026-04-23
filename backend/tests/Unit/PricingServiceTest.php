<?php

namespace Tests\Unit;

use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Services\PricingService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class PricingServiceTest extends TestCase
{
    protected PricingService $pricingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricingService = new PricingService();
    }

    public function test_calculates_correct_price_for_period()
    {
        $category = new VehicleCategory(['daily_rate' => 100]);
        $vehicle = new Vehicle();
        $vehicle->setRelation('category', $category);

        $startDate = Carbon::parse('2024-01-01 10:00:00');
        $endDate = Carbon::parse('2024-01-03 10:00:00'); // 2 days

        $price = $this->pricingService->calculate($vehicle, $startDate, $endDate);

        $this->assertEquals(200, $price);
    }

    public function test_calculates_correct_late_fees()
    {
        $lateFees = $this->pricingService->calculateLateFees(100, 2); // 100 * 1.2 * 2

        $this->assertEquals(240, $lateFees);
    }
}
