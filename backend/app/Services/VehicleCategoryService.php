<?php

namespace App\Services;

use App\Models\VehicleCategory;

class VehicleCategoryService
{
    public function getAll()
    {
        return VehicleCategory::all();
    }

    public function create(array $data): VehicleCategory
    {
        return VehicleCategory::create($data);
    }

    public function update(VehicleCategory $category, array $data): VehicleCategory
    {
        $category->update($data);
        return $category;
    }

    public function delete(VehicleCategory $category): void
    {
        $category->delete();
    }
}
