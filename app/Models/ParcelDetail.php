<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcelDetail extends Model
{
    use HasFactory;

    protected $table = 'parcelsdetails';
    protected $primaryKey = 'detailId';
    public $timestamps = false;

    protected $fillable = [
        'detailQun',
        'detailInfo',
        'parcelId',
    ];

    protected $casts = [
        'detailId' => 'integer',
        'detailQun' => 'integer',
        'parcelId' => 'integer',
    ];

    public function parcel()
    {
        return $this->belongsTo(Parcel::class, 'parcelId', 'parcelId');
    }

    public function driverParcelDetails()
    {
        return $this->hasMany(DriverParcelDetail::class, 'parcelDetailId', 'detailId');
    }

    /**
     * Get available quantity for this parcel detail.
     */
    public function getAvailableQuantity(): int
    {
        $assignedQuantity = $this->driverParcelDetails()
            ->whereHas('driverParcel', function ($query) {
                $query->whereIn('status', ['pending', 'in_transit']);
            })
            ->sum('quantityTaken');

        return max(0, $this->detailQun - $assignedQuantity);
    }

    /**
     * Check if quantity is available.
     */
    public function hasAvailableQuantity(int $quantity): bool
    {
        return $this->getAvailableQuantity() >= $quantity;
    }

    /**
     * Search available parcel details.
     */
    public static function searchAvailable(string $searchTerm = '', int $limit = 20): array
    {
        $query = self::with(['parcel.customer', 'parcel.originOffice', 'parcel.destinationOffice'])
            ->whereHas('parcel', function ($q) use ($searchTerm) {
                $q->where('accept', '!=', 'no');
            });

        // Apply search conditions with OR logic
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                // Search in detailInfo
                $q->where('detailInfo', 'like', "%{$searchTerm}%")
                    // OR search in parcel number
                    ->orWhereHas('parcel', function ($parcelQuery) use ($searchTerm) {
                        $parcelQuery->where('parcelNumber', 'like', "%{$searchTerm}%");
                    })
                    // OR search in customer name or passport
                    ->orWhereHas('parcel.customer', function ($customerQuery) use ($searchTerm) {
                        $customerQuery->where('FName', 'like', "%{$searchTerm}%")
                            ->orWhere('LName', 'like', "%{$searchTerm}%")
                            ->orWhere('customerPassport', 'like', "%{$searchTerm}%");
                    });
            });
        }

        return $query->limit($limit)
            ->get()
            ->map(function ($detail) {
                $availableQuantity = $detail->getAvailableQuantity();
                
                return [
                    'detailId' => $detail->detailId,
                    'parcelId' => $detail->parcelId,
                    'parcelNumber' => $detail->parcel->parcelNumber ?? '',
                    'customerName' => ($detail->parcel->customer->FName ?? '') . ' ' . ($detail->parcel->customer->LName ?? ''),
                    'customerPassport' => $detail->parcel->customer->customerPassport ?? '',
                    'detailInfo' => $detail->detailInfo,
                    'totalQuantity' => $detail->detailQun,
                    'availableQuantity' => $availableQuantity,
                ];
            })
            ->filter(function ($item) {
                return $item['availableQuantity'] > 0;
            })
            ->values()
            ->toArray();
    }

    /**
     * Find parcel detail by ID.
     */
    public static function findById(int $id): ?self
    {
        return self::find($id);
    }
}
