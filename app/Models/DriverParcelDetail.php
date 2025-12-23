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
        'leftOfficeAt',
    ];

    protected $casts = [
        'detailId' => 'integer',
        'detailQun' => 'integer',
        'parcelId' => 'integer',
        'parcelDetailId' => 'integer',
        'quantityTaken' => 'integer',
        'isArrived' => 'boolean',
        'arrivedAt' => 'datetime',
        'leftOfficeAt' => 'datetime',
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
     * Mark item as left office (in transit).
     */
    public function markAsLeftOffice(): bool
    {
        if ($this->leftOfficeAt) {
            return false;
        }

        $this->update([
            'leftOfficeAt' => now(),
        ]);

        // Create tracking record for this item leaving
        $this->createTrackingForLeaving();

        return true;
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

        // Create tracking record for arrival
        $this->createTrackingForArrival();

        // Check if driver parcel should be marked as arrived (only if ALL items arrived)
        $this->driverParcel->markAsArrivedIfComplete();

        return true;
    }

    /**
     * Create tracking record when item leaves office.
     */
    protected function createTrackingForLeaving(): void
    {
        $parcelDetail = $this->parcelDetail;
        if (! $parcelDetail || ! $parcelDetail->parcel) {
            return;
        }

        $driverParcel = $this->driverParcel;
        $office = $driverParcel->office;

        ParcelTracking::createTracking(
            $parcelDetail->parcel->parcelId,
            $driverParcel->parcelId,
            $driverParcel->tripId,
            'in_transit',
            $office ? $office->officeName : null,
            'تم إرسال العنصر من المكتب',
            'system',
            $this->detailId
        );
    }

    /**
     * Create tracking record when item arrives.
     */
    protected function createTrackingForArrival(): void
    {
        $parcelDetail = $this->parcelDetail;
        if (! $parcelDetail || ! $parcelDetail->parcel) {
            return;
        }

        $driverParcel = $this->driverParcel;
        $office = $driverParcel->office;

        // Only create "arrived" tracking if ALL items have arrived
        if ($driverParcel->allItemsArrived()) {
            ParcelTracking::createTracking(
                $parcelDetail->parcel->parcelId,
                $driverParcel->parcelId,
                $driverParcel->tripId,
                'arrived',
                $office ? $office->officeName : null,
                'وصل جميع العناصر إلى الوجهة',
                'system',
                $this->detailId
            );
        }
    }

    /**
     * Mark item as not arrived.
     */
    public function markAsNotArrived(): bool
    {
        if (! $this->isArrived) {
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
