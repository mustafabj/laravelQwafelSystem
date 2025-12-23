<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function trackingHistory()
    {
        return $this->hasMany(ParcelTracking::class, 'driverParcelId', 'parcelId');
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
        // Only mark as arrived if ALL items have arrived
        if ($this->allItemsArrived() && $this->status !== 'arrived') {
            $this->update([
                'status' => 'arrived',
                'arrivedAt' => now(),
            ]);

            // Create tracking record for arrival (only when all items arrived)
            $this->createTrackingRecord('arrived', 'وصل جميع العناصر إلى الوجهة', 'system');

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
        return DB::transaction(function () use ($data, $user, $parcelDetails) {
            // Create driver parcel
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

            // Load all parcel details in one query
            $parcelDetailIds = array_column($parcelDetails, 'parcelDetailId');
            $parcelDetailsMap = ParcelDetail::whereIn('detailId', $parcelDetailIds)
                ->get()
                ->keyBy('detailId');

            // Batch check available quantities efficiently
            $assignedQuantities = DriverParcelDetail::whereIn('parcelDetailId', $parcelDetailIds)
                ->whereHas('driverParcel', function ($query) {
                    $query->whereIn('status', ['pending', 'in_transit']);
                })
                ->select('parcelDetailId', DB::raw('SUM(quantityTaken) as totalAssigned'))
                ->groupBy('parcelDetailId')
                ->pluck('totalAssigned', 'parcelDetailId')
                ->toArray();

            // Prepare batch insert data and track quantities being added in this request
            $driverParcelDetailsData = [];
            $processedQuantities = []; // Track quantities processed in this loop

            foreach ($parcelDetails as $detailData) {
                $parcelDetailId = $detailData['parcelDetailId'];
                $parcelDetail = $parcelDetailsMap->get($parcelDetailId);

                if (! $parcelDetail) {
                    throw new \Exception("تفاصيل الإرسالية ({$parcelDetailId}) غير موجودة");
                }

                // Check available quantity efficiently
                // Account for: already assigned quantities + quantities being processed in this request
                $assignedQuantity = $assignedQuantities[$parcelDetailId] ?? 0;
                $processedInRequest = $processedQuantities[$parcelDetailId] ?? 0;
                $availableQuantity = max(0, $parcelDetail->detailQun - $assignedQuantity - $processedInRequest);

                if ($availableQuantity < $detailData['quantityTaken']) {
                    throw new \Exception("الكمية المطلوبة ({$detailData['quantityTaken']}) تتجاوز الكمية المتاحة ({$availableQuantity})");
                }

                // Track this quantity for subsequent items with the same parcelDetailId
                $processedQuantities[$parcelDetailId] = ($processedQuantities[$parcelDetailId] ?? 0) + $detailData['quantityTaken'];

                $driverParcelDetailsData[] = [
                    'parcelId' => $driverParcel->parcelId,
                    'parcelDetailId' => $parcelDetailId,
                    'quantityTaken' => $detailData['quantityTaken'],
                    'detailQun' => $detailData['quantityTaken'],
                    'detailInfo' => $parcelDetail->detailInfo,
                    'isArrived' => false,
                ];
            }

            // Batch insert all driver parcel details
            if (! empty($driverParcelDetailsData)) {
                DriverParcelDetail::insert($driverParcelDetailsData);
            }

            // Create initial tracking record for each parcel (not per item, per parcel)
            $driverParcel->createTrackingRecord('pending', 'تم إنشاء الإرسالية للسائق');

            return $driverParcel;
        });
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

        $oldStatus = $driverParcel->status;
        $updateData = ['status' => $status];

        if ($status === 'arrived') {
            $updateData['arrivedAt'] = now();
        }

        if ($delayReason !== null) {
            $updateData['delayReason'] = $delayReason;
        }

        $updated = $driverParcel->update($updateData);

        // Create tracking record if status changed
        if ($updated && $oldStatus !== $status) {
            $driverParcel->createTrackingRecord($status, $delayReason);
        }

        return $updated;
    }

    /**
     * Update status with tracking.
     */
    public function updateStatus(string $status, ?string $delayReason = null, ?string $trackedBy = null): bool
    {
        $oldStatus = $this->status;
        $updateData = ['status' => $status];

        if ($status === 'arrived') {
            // Only set arrivedAt if ALL items have arrived
            if ($this->allItemsArrived()) {
                $updateData['arrivedAt'] = now();
            } else {
                // Don't allow setting to arrived if not all items arrived
                return false;
            }
        }

        if ($delayReason !== null) {
            $updateData['delayReason'] = $delayReason;
        }

        $updated = $this->update($updateData);

        // If status changed to in_transit, mark all items as left office
        if ($updated && $oldStatus !== $status && $status === 'in_transit') {
            $this->details()->whereNull('leftOfficeAt')->each(function ($detail) {
                $detail->markAsLeftOffice();
            });
        }

        // Create tracking record if status changed (but not for arrived unless all items arrived)
        if ($updated && $oldStatus !== $status) {
            if ($status === 'arrived' && ! $this->allItemsArrived()) {
                // Don't create tracking for arrived if not all items arrived
                return $updated;
            }
            $this->createTrackingRecord($status, $delayReason, $trackedBy);
        }

        return $updated;
    }

    /**
     * Create a tracking record for this driver parcel.
     */
    public function createTrackingRecord(?string $status = null, ?string $description = null, ?string $trackedBy = null): ParcelTracking
    {
        $status = $status ?? $this->status;
        $location = $this->office ? $this->office->officeName : null;
        $trackedBy = $trackedBy ?? (Auth::check() ? Auth::user()->name : 'system');

        // Get all parcels associated with this driver parcel through details
        $parcelIds = $this->details()
            ->with('parcelDetail.parcel')
            ->get()
            ->pluck('parcelDetail.parcel.parcelId')
            ->filter()
            ->unique()
            ->toArray();

        // Create tracking records for each associated parcel
        $trackingRecords = [];
        foreach ($parcelIds as $parcelId) {
            $trackingRecords[] = ParcelTracking::createTracking(
                $parcelId,
                $this->parcelId,
                $this->tripId,
                $status,
                $location,
                $description,
                $trackedBy
            );
        }

        return $trackingRecords[0] ?? ParcelTracking::createTracking(
            $parcelIds[0] ?? 0,
            $this->parcelId,
            $this->tripId,
            $status,
            $location,
            $description,
            $trackedBy
        );
    }

    /**
     * Get parcels for a customer by phone number.
     */
    public static function getParcelsByCustomerPhone(string $phoneNumber): \Illuminate\Database\Eloquent\Collection
    {
        // Normalize phone number (remove spaces, dashes, etc.)
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        $parcels = self::whereHas('details.parcelDetail.parcel.customer', function ($query) use ($phoneNumber) {
            $query->where(function ($q) use ($phoneNumber) {
                $q->where('phone1', 'like', "%{$phoneNumber}%")
                    ->orWhere('phone2', 'like', "%{$phoneNumber}%")
                    ->orWhere('phone3', 'like', "%{$phoneNumber}%")
                    ->orWhere('phone4', 'like', "%{$phoneNumber}%");
            });
        })
            ->with([
                'trip',
                'office',
                'details.parcelDetail.parcel.customer',
                'details.parcelDetail.parcel.originOffice',
                'details.parcelDetail.parcel.destinationOffice',
            ])
            ->orderBy('parcelDate', 'desc')
            ->get();

        // Calculate effective status for each parcel based on customer's items
        return $parcels->map(function ($parcel) use ($phoneNumber) {
            $customerItems = $parcel->details()
                ->whereHas('parcelDetail.parcel.customer', function ($query) use ($phoneNumber) {
                    $query->where(function ($q) use ($phoneNumber) {
                        $q->where('phone1', 'like', "%{$phoneNumber}%")
                            ->orWhere('phone2', 'like', "%{$phoneNumber}%")
                            ->orWhere('phone3', 'like', "%{$phoneNumber}%")
                            ->orWhere('phone4', 'like', "%{$phoneNumber}%");
                    });
                })
                ->get();

            $allCustomerItemsArrived = $customerItems->where('isArrived', false)->isEmpty();
            $anyCustomerItemLeft = $customerItems->whereNotNull('leftOfficeAt')->isNotEmpty();

            // Calculate effective status - simple: arrived or not
            $effectiveStatus = 'pending';
            if ($allCustomerItemsArrived && $customerItems->isNotEmpty()) {
                $effectiveStatus = 'arrived';
            } elseif ($anyCustomerItemLeft) {
                $effectiveStatus = 'in_transit';
            }

            $parcel->effectiveStatus = $effectiveStatus;

            return $parcel;
        });
    }
}
