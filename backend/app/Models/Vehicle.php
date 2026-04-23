<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'brand',
        'model',
        'year',
        'license_plate',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(VehicleCategory::class, 'category_id');
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
