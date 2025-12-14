<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverParcelDetail extends Model
{
    use HasFactory;

    protected $table = 'driverparceldetails';
    protected $primaryKey = 'detailId';
    public $timestamps = false;

    protected $fillable = [
        'detailQun',
        'detailInfo',
        'parcelId',
        'parcelDetailId',
        'quantityTaken',
        'isArrived',
        'arrivedAt',
    ];

    protected $casts = [
        'detailId' => 'integer',
        'detailQun' => 'integer',
        'parcelId' => 'integer',
        'parcelDetailId' => 'integer',
        'quantityTaken' => 'integer',
        'isArrived' => 'boolean',
        'arrivedAt' => 'datetime',
    ];

    public function driverParcel()
    {
        return $this->belongsTo(DriverParcel::class, 'parcelId', 'parcelId');
    }

    public function parcelDetail()
    {
        return $this->belongsTo(ParcelDetail::class, 'parcelDetailId', 'detailId');
    }

    /**
     * Mark item as arrived.
     */
    public function markAsArrived(): bool
    {
        if ($this->isArrived) {
            return false;
        }

        $this->update([
            'isArrived' => true,
            'arrivedAt' => now(),
        ]);

        // Check if driver parcel should be marked as arrived
        $this->driverParcel->markAsArrivedIfComplete();

        return true;
    }

    /**
     * Mark item as not arrived.
     */
    public function markAsNotArrived(): bool
    {
        if (!$this->isArrived) {
            return false;
        }

        $this->update([
            'isArrived' => false,
            'arrivedAt' => null,
        ]);

        return true;
    }

    /**
     * Find driver parcel detail by ID and verify it belongs to driver parcel.
     */
    public static function findByDriverParcel(int $detailId, int $driverParcelId): ?self
    {
        return self::with('driverParcel')
            ->where('detailId', $detailId)
            ->whereHas('driverParcel', function ($query) use ($driverParcelId) {
                $query->where('parcelId', $driverParcelId);
            })
            ->first();
    }
}
