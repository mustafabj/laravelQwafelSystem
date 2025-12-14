<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DriverParcel extends Model
{
    use HasFactory;

    protected $table = 'driverparcels';

    protected $primaryKey = 'parcelId';

    public $timestamps = false;

    protected $fillable = [
        'parcelNumber',
        'driverName',
        'parcelDate',
        'cost',
        'paid',
        'costRest',
        'driverNumber',
        'currency',
        'userId',
        'sendTo',
        'officeId',
        'token',
        'tripId',
        'tripDate',
        'status',
        'arrivedAt',
        'delayReason',
    ];

    protected $casts = [
        'parcelId' => 'integer',
        'parcelNumber' => 'integer',
        'userId' => 'integer',
        'officeId' => 'integer',
        'tripId' => 'integer',
        'cost' => 'float',
        'paid' => 'float',
        'costRest' => 'float',
        'arrivedAt' => 'datetime',
        'tripDate' => 'date',
    ];

    /**
     * Relationships
     */

    // Items under this driver parcel
    public function details()
    {
        return $this->hasMany(DriverParcelDetail::class, 'parcelId', 'parcelId');
    }

    // Office that owns this parcel
    public function office()
    {
        return $this->belongsTo(Office::class, 'officeId', 'officeId');
    }

    // User who created or assigned this parcel
    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'tripId', 'tripId');
    }

    public static function getLastDriverParcels()
    {
        $user = Auth::user();
        $query = self::with(['user', 'office', 'trip']);

        if ($user->role !== 'admin') {
            $query->where(function ($sub) use ($user) {
                $sub->where('userId', $user->id)
                    ->orWhere('sendTo', $user->officeId)
                    ->orWhere('officeId', $user->officeId);
            });
        }

        return $query->latest('parcelDate')->limit(100)->get();
    }

    /**
     * Scope for filtering by user office.
     */
    public function scopeForUser($query, $user)
    {
        if ($user->role !== 'admin') {
            return $query->where(function ($sub) use ($user) {
                $sub->where('userId', $user->id)
                    ->orWhere('officeId', $user->office_id ?? 0);
            });
        }

        return $query;
    }

    /**
     * Check if all items have arrived.
     */
    public function allItemsArrived(): bool
    {
        return $this->details()->where('isArrived', false)->count() === 0;
    }

    /**
     * Mark as arrived if all items have arrived.
     */
    public function markAsArrivedIfComplete(): bool
    {
        if ($this->allItemsArrived() && $this->status !== 'arrived') {
            $this->update([
                'status' => 'arrived',
                'arrivedAt' => now(),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Get next parcel number.
     */
    public static function getNextParcelNumber(): int
    {
        $lastParcel = self::orderBy('parcelNumber', 'desc')->first();

        return $lastParcel && $lastParcel->parcelNumber >= 1
            ? $lastParcel->parcelNumber + 1
            : 1;
    }

    /**
     * Get filtered list of driver parcels for a user.
     */
    public static function getFilteredList($user, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = self::with(['trip', 'office', 'user', 'details.parcelDetail.parcel']);

        if ($user->role !== 'admin') {
            $query->where(function ($sub) use ($user) {
                $sub->where('userId', $user->id)
                    ->orWhere('officeId', $user->office_id ?? 0);
            });
        }

        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['tripId']) && $filters['tripId']) {
            $query->where('tripId', $filters['tripId']);
        }

        return $query->latest('parcelDate')->paginate(50);
    }

    /**
     * Find driver parcel with all relations.
     */
    public static function findWithRelations(int $id): ?self
    {
        return self::with([
            'trip',
            'office',
            'user',
            'details.parcelDetail.parcel.customer',
        ])->find($id);
    }

    /**
     * Create driver parcel with details.
     */
    public static function createWithDetails(array $data, $user, array $parcelDetails): self
    {
        $driverParcel = self::create([
            'parcelNumber' => $data['parcelNumber'],
            'tripId' => $data['tripId'],
            'tripDate' => $data['tripDate'],
            'driverName' => $data['driverName'],
            'driverNumber' => $data['driverNumber'],
            'sendTo' => $data['sendTo'],
            'officeId' => $data['officeId'],
            'parcelDate' => now()->format('Y-m-d H:i:s'),
            'cost' => $data['cost'] ?? 0,
            'paid' => $data['paid'] ?? 0,
            'costRest' => $data['costRest'] ?? 0,
            'currency' => $data['currency'] ?? 'IQD',
            'userId' => $user->id,
            'status' => 'pending',
        ]);

        foreach ($parcelDetails as $detailData) {
            $parcelDetail = ParcelDetail::findById($detailData['parcelDetailId']);

            if (! $parcelDetail->hasAvailableQuantity($detailData['quantityTaken'])) {
                throw new \Exception("الكمية المطلوبة ({$detailData['quantityTaken']}) تتجاوز الكمية المتاحة ({$parcelDetail->getAvailableQuantity()})");
            }

            DriverParcelDetail::create([
                'parcelId' => $driverParcel->parcelId,
                'parcelDetailId' => $detailData['parcelDetailId'],
                'quantityTaken' => $detailData['quantityTaken'],
                'detailQun' => $detailData['quantityTaken'],
                'detailInfo' => $parcelDetail->detailInfo,
                'isArrived' => false,
            ]);
        }

        return $driverParcel;
    }

    /**
     * Update status by ID.
     */
    public static function updateStatusById(int $id, string $status, ?string $delayReason = null): bool
    {
        $driverParcel = self::find($id);

        if (! $driverParcel) {
            return false;
        }

        $updateData = ['status' => $status];

        if ($status === 'arrived') {
            $updateData['arrivedAt'] = now();
        }

        if ($delayReason !== null) {
            $updateData['delayReason'] = $delayReason;
        }

        return $driverParcel->update($updateData);
    }
}
