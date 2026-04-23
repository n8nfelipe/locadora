<?php

namespace App\Services;

use App\Models\Rental;
use App\Models\Reservation;
use App\Repositories\RentalRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RentalService
{
    public function __construct(
        protected RentalRepository $repository,
        protected PricingService $pricingService
    ) {
    }

    public function checkout(Reservation $reservation): Rental
    {
        return DB::transaction(function () use ($reservation) {
            if ($reservation->status !== 'active') {
                throw ValidationException::withMessages([
                    'reservation_id' => ['Esta reserva não está ativa.'],
                ]);
            }

            $vehicle = $reservation->vehicle;
            if ($vehicle->status !== 'available') {
                throw ValidationException::withMessages([
                    'vehicle_id' => ['O veículo já está em uso.'],
                ]);
            }

            $totalPrice = $this->pricingService->calculate(
                $vehicle,
                $reservation->start_date,
                $reservation->end_date
            );

            $rental = $this->repository->create([
                'reservation_id' => $reservation->id,
                'user_id' => $reservation->user_id,
                'vehicle_id' => $reservation->vehicle_id,
                'start_date' => $reservation->start_date,
                'end_date' => $reservation->end_date,
                'total_price' => $totalPrice,
                'status' => 'ongoing',
            ]);

            $reservation->update(['status' => 'completed']);
            $vehicle->update(['status' => 'rented']);

            return $rental;
        });
    }

    public function checkin(Rental $rental): Rental
    {
        return DB::transaction(function () use ($rental) {
            if ($rental->status !== 'ongoing') {
                return $rental;
            }

            $actualReturnDate = now();

            $lateDays = $rental->end_date->diffInDays($actualReturnDate, false);

            $status = 'finished';
            $totalPrice = $rental->total_price;

            if ($lateDays > 0) {
                $lateFees = $this->pricingService->calculateLateFees(
                    $rental->vehicle->category->daily_rate,
                    (int) $lateDays
                );
                $totalPrice += $lateFees;
                $status = 'late';
            }

            $rental = $this->repository->update($rental, [
                'actual_return_date' => $actualReturnDate,
                'total_price' => $totalPrice,
                'status' => $status,
            ]);

            $rental->vehicle->update(['status' => 'available']);

            return $rental;
        });
    }
}
