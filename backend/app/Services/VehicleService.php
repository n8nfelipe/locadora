<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Repositories\VehicleRepository;
use Illuminate\Support\Facades\Cache;

class VehicleService
{
    public function __construct(
        protected VehicleRepository $repository
    ) {
    }

    public function getAll(array $filters = [])
    {
        return $this->repository->all($filters);
    }

    public function getAvailable()
    {
        return Cache::remember('available_vehicles', 3600, function () {
            return $this->repository->findAvailable();
        });
    }

    public function create(array $data): Vehicle
    {
        $vehicle = $this->repository->create($data);
        $this->clearCache();
        return $vehicle;
    }

    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle = $this->repository->update($vehicle, $data);
        $this->clearCache();
        return $vehicle;
    }

    public function delete(Vehicle $vehicle): void
    {
        $this->repository->delete($vehicle);
        $this->clearCache();
    }

    protected function clearCache(): void
    {
        Cache::forget('available_vehicles');
    }
}
