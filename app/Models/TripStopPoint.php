<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripStopPoint extends Model
{
    use HasFactory;

    protected $table = 'trip_stop_points';

    protected $primaryKey = 'stopPointId';

    public $timestamps = true;

    protected $fillable = [
        'tripId',
        'stopName',
        'arrivalTime',
        'order',
    ];

    protected $casts = [
        'stopPointId' => 'integer',
        'tripId' => 'integer',
        'order' => 'integer',
        'arrivalTime' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function trip()
    {
        return $this->belongsTo(Trip::class, 'tripId', 'tripId');
    }

    /**
     * Get stop points for a trip ordered by order field.
     */
    public static function getByTrip(int $tripId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('tripId', $tripId)
            ->orderBy('order')
            ->orderBy('arrivalTime')
            ->get();
    }

    /**
     * Create stop points for a trip.
     */
    public static function createForTrip(int $tripId, array $stopPoints): void
    {
        foreach ($stopPoints as $index => $stopPoint) {
            self::create([
                'tripId' => $tripId,
                'stopName' => $stopPoint['stopName'],
                'arrivalTime' => $stopPoint['arrivalTime'],
                'order' => $index + 1,
            ]);
        }
    }
}
