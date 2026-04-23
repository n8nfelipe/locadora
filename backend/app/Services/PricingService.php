<?php

namespace App\Services;

use App\Models\Vehicle;
use Carbon\Carbon;

class PricingService
{
    public function calculate(Vehicle $vehicle, Carbon $startDate, Carbon $endDate): float
    {
        $days = $startDate->diffInDays($endDate);
        if ($days <= 0) {
            $days = 1;
        }

        $dailyRate = $vehicle->category->daily_rate;

        return $days * $dailyRate;
    }

    public function calculateLateFees(float $dailyRate, int $lateDays): float
    {
        if ($lateDays <= 0) {
            return 0;
        }

        // Penalty of 20% on top of daily rate for late days
        return $lateDays * ($dailyRate * 1.2);
    }
}
