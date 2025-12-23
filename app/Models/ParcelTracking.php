<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcelTracking extends Model
{
    use HasFactory;

    protected $table = 'parcel_trackings';

    protected $fillable = [
        'parcelId',
        'driverParcelId',
        'driverParcelDetailId',
        'tripId',
        'status',
        'location',
        'description',
        'trackedAt',
        'trackedBy',
    ];

    protected $casts = [
        'parcelId' => 'integer',
        'driverParcelId' => 'integer',
        'driverParcelDetailId' => 'integer',
        'tripId' => 'integer',
        'trackedAt' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function parcel()
    {
        return $this->belongsTo(Parcel::class, 'parcelId', 'parcelId');
    }

    public function driverParcel()
    {
        return $this->belongsTo(DriverParcel::class, 'driverParcelId', 'parcelId');
    }

    public function driverParcelDetail()
    {
        return $this->belongsTo(DriverParcelDetail::class, 'driverParcelDetailId', 'detailId');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'tripId', 'tripId');
    }

    /**
     * Create a tracking record for a parcel status change.
     */
    public static function createTracking(
        int $parcelId,
        ?int $driverParcelId,
        ?int $tripId,
        string $status,
        ?string $location = null,
        ?string $description = null,
        ?string $trackedBy = 'system',
        ?int $driverParcelDetailId = null
    ): self {
        return self::create([
            'parcelId' => $parcelId,
            'driverParcelId' => $driverParcelId,
            'driverParcelDetailId' => $driverParcelDetailId,
            'tripId' => $tripId,
            'status' => $status,
            'location' => $location,
            'description' => $description,
            'trackedAt' => now(),
            'trackedBy' => $trackedBy,
        ]);
    }

    /**
     * Get tracking history for a parcel.
     */
    public static function getTrackingHistory(int $parcelId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('parcelId', $parcelId)
            ->with(['trip', 'driverParcel'])
            ->orderBy('trackedAt', 'desc')
            ->get();
    }
}
