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
        'officeId',
        'destination',
        'finalArrivalTime',
        'daysOfWeek',
        'times',
        'isActive',
        'notes',
        'createdBy',
    ];

    protected $casts = [
        'tripId' => 'integer',
        'officeId' => 'integer',
        'createdBy' => 'integer',
        'isActive' => 'boolean',
        'daysOfWeek' => 'array',
        'times' => 'array',
        'finalArrivalTime' => 'datetime',
    ];

    /**
     * Relationships
     */
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

    public function stopPoints()
    {
        return $this->hasMany(TripStopPoint::class, 'tripId', 'tripId')->orderBy('order');
    }

    /**
     * Get all active trips with relations.
     */
    public static function getActiveTrips(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('isActive', true)
            ->with(['office'])
            ->get();
    }

    /**
     * Get active trips for dropdown.
     */
    public static function getActiveForDropdown(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('isActive', true)
            ->get();
    }

    /**
     * Find trip by ID.
     */
    public static function findById(int $id): ?self
    {
        return self::find($id);
    }

    /**
     * Get all trips with relations.
     */
    public static function getAllWithRelations(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return self::with(['office', 'creator', 'stopPoints'])
            ->latest('created_at')
            ->paginate(20);
    }

    /**
     * Create a new trip.
     */
    public static function createTrip(array $data, int $createdBy): self
    {
        return self::create([
            'tripName' => $data['tripName'],
            'officeId' => $data['officeId'],
            'destination' => $data['destination'],
            'finalArrivalTime' => $data['finalArrivalTime'] ?? null,
            'daysOfWeek' => $data['daysOfWeek'],
            'times' => $data['times'],
            'isActive' => $data['isActive'] ?? true,
            'notes' => $data['notes'] ?? null,
            'createdBy' => $createdBy,
        ]);
    }
}
