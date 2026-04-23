<?php

namespace App\Services;

use App\Models\Reservation;
use App\Repositories\ReservationRepository;
use App\Repositories\VehicleRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    public function __construct(
        protected ReservationRepository $repository,
        protected VehicleRepository $vehicleRepository
    ) {
    }

    public function create(array $data): Reservation
    {
        return DB::transaction(function () use ($data) {
            $vehicle = $this->vehicleRepository->findById($data['vehicle_id']);

            if ($vehicle->status !== 'available') {
                throw ValidationException::withMessages([
                    'vehicle_id' => ['Este veículo não está disponível para reserva.'],
                ]);
            }

            return $this->repository->create([
                'user_id' => $data['user_id'],
                'vehicle_id' => $data['vehicle_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => 'active',
            ]);
        });
    }

    public function cancel(Reservation $reservation): void
    {
        $this->repository->update($reservation, ['status' => 'cancelled']);
    }

    public function expireReservations(): void
    {
        $this->repository->expireOld(2);
    }
}
