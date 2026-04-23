<?php

namespace App\Repositories;

use App\Models\Reservation;

class ReservationRepository
{
    public function create(array $data): Reservation
    {
        return Reservation::create($data);
    }

    public function findById(string $id): Reservation
    {
        return Reservation::findOrFail($id);
    }

    public function update(Reservation $reservation, array $data): Reservation
    {
        $reservation->update($data);
        return $reservation;
    }

    public function expireOld(int $hours): int
    {
        return Reservation::where('status', 'active')
            ->where('created_at', '<=', now()->subHours($hours))
            ->update(['status' => 'expired']);
    }
}
