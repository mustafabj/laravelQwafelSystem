<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $table = 'trips';
    protected $primaryKey = 'tripId';
    public $timestamps = true;

    protected $fillable = [
        'tripName',
        'driverId',
        'officeId',
        'destination',
        'daysOfWeek',
        'times',
        'isActive',
        'notes',
        'createdBy',
    ];

    protected $casts = [
        'tripId' => 'integer',
        'driverId' => 'integer',
        'officeId' => 'integer',
        'createdBy' => 'integer',
        'isActive' => 'boolean',
        'daysOfWeek' => 'array',
        'times' => 'array',
    ];

    /**
     * Relationships
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driverId', 'driverId');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'officeId', 'officeId');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'createdBy', 'id');
    }

    public function driverParcels()
    {
        return $this->hasMany(DriverParcel::class, 'tripId', 'tripId');
    }

    /**
     * Get all active trips with relations.
     */
    public static function getActiveTrips(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('isActive', true)
            ->with(['driver', 'office'])
            ->get();
    }

    /**
     * Get active trips for dropdown.
     */
    public static function getActiveForDropdown(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('isActive', true)
            ->with('driver')
            ->get();
    }

    /**
     * Find trip by ID.
     */
    public static function findById(int $id): ?self
    {
        return self::find($id);
    }
}

