<?php

namespace App\Repositories;

use App\Models\Rental;

class RentalRepository
{
    public function create(array $data): Rental
    {
        return Rental::create($data);
    }

    public function findById(string $id): Rental
    {
        return Rental::findOrFail($id);
    }

    public function update(Rental $rental, array $data): Rental
    {
        $rental->update($data);
        return $rental;
    }
}
