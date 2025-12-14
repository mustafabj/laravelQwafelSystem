<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $table = 'driver';
    protected $primaryKey = 'driverId';
    public $timestamps = false;

    protected $fillable = [
        'driverName',
        'driverPhone',
        'driverLicense',
        'officeId',
    ];

    protected $casts = [
        'driverId' => 'integer',
        'officeId' => 'integer',
    ];

    /**
     * Relationships
     */


    // Parcels assigned to this driver
    public function parcels()
    {
        return $this->hasMany(DriverParcel::class, 'driverId', 'driverId');
    }

    /**
     * Get all drivers.
     */
    public static function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return self::all();
    }
}
