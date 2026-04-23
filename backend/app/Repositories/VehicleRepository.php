<?php

namespace App\Repositories;

use App\Models\Vehicle;

class VehicleRepository
{
    public function all(array $filters = [])
    {
        $query = Vehicle::with('category');

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(15);
    }

    public function findAvailable()
    {
        return Vehicle::with('category')->where('status', 'available')->get();
    }

    public function findById(string $id)
    {
        return Vehicle::with('category')->findOrFail($id);
    }

    public function create(array $data)
    {
        return Vehicle::create($data);
    }

    public function update(Vehicle $vehicle, array $data)
    {
        $vehicle->update($data);
        return $vehicle;
    }

    public function delete(Vehicle $vehicle)
    {
        return $vehicle->delete();
    }
}
