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
                    $query->whereIn('status', ['pending', 'not_started', 'in_transit']);
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
            $parcel->effectiveStatus = $parcel->calculateEffectiveStatusForCustomer($phoneNumber);

            return $parcel;
        });
    }

    /**
     * Get tracking data for a customer.
     */
    public function getTrackingDataForCustomer(string $phoneNumber): array
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Get customer's items in this driver parcel
        $customerItems = $this->details()
            ->whereHas('parcelDetail.parcel.customer', function ($query) use ($phoneNumber) {
                $query->where(function ($q) use ($phoneNumber) {
                    $q->where('phone1', 'like', "%{$phoneNumber}%")
                        ->orWhere('phone2', 'like', "%{$phoneNumber}%")
                        ->orWhere('phone3', 'like', "%{$phoneNumber}%")
                        ->orWhere('phone4', 'like', "%{$phoneNumber}%");
                });
            })
            ->with('parcelDetail.parcel')
            ->get();

        $customerItemIds = $customerItems->pluck('detailId')->toArray();
        $customerParcelIds = $customerItems->pluck('parcelDetail.parcel.parcelId')->filter()->unique()->toArray();

        // Get tracking history for customer's items only
        $trackingHistory = ParcelTracking::getTrackingHistoryForCustomer(
            $customerParcelIds,
            $this->parcelId,
            $customerItemIds
        );

        // Calculate effective status
        $effectiveStatus = $this->calculateEffectiveStatusForCustomer($phoneNumber);
        $allCustomerItemsArrived = $customerItems->where('isArrived', false)->isEmpty();

        return [
            'trackingHistory' => $trackingHistory,
            'effectiveStatus' => $effectiveStatus,
            'allCustomerItemsArrived' => $allCustomerItemsArrived,
        ];
    }

    /**
     * Calculate effective status for a customer based on their items.
     */
    public function calculateEffectiveStatusForCustomer(string $phoneNumber): string
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        $customerItems = $this->details()
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

        // Calculate effective status
        if ($allCustomerItemsArrived && $customerItems->isNotEmpty()) {
            return 'arrived';
        } elseif ($anyCustomerItemLeft) {
            return 'in_transit';
        }

        return 'pending';
    }

    /**
     * Check if parcel belongs to customer by phone number.
     */
    public function belongsToCustomerByPhone(string $phoneNumber): bool
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        return $this->details()
            ->whereHas('parcelDetail.parcel.customer', function ($query) use ($phoneNumber) {
                $query->where(function ($q) use ($phoneNumber) {
                    $q->where('phone1', 'like', "%{$phoneNumber}%")
                        ->orWhere('phone2', 'like', "%{$phoneNumber}%")
                        ->orWhere('phone3', 'like', "%{$phoneNumber}%")
                        ->orWhere('phone4', 'like', "%{$phoneNumber}%");
                });
            })
            ->exists();
    }

    /**
     * Get trip management data with all calculations and prepared data for view.
     */
    public function getTripManagementData(): array
    {
        $trip = $this->trip;
        if (! $trip) {
            return [
                'stopPoints' => [],
                'isCompleted' => false,
                'isActive' => false,
            ];
        }

        // Auto-update status based on tripDate
        $this->updateStatusBasedOnTripDate();

        // Get arrivals for this driver parcel
        $arrivals = TripStopPointArrival::where('driverParcelId', $this->parcelId)
            ->with(['stopPoint', 'approver'])
            ->get()
            ->keyBy('stopPointId');

        // Process stop points with all calculations
        $previousExpectedTime = null;
        $stopPointsData = collect();

        foreach ($trip->stopPoints as $stopPoint) {
            $arrival = $arrivals->get($stopPoint->stopPointId);
            $stopPointData = $stopPoint->prepareForTripManagement($this, $arrival, $previousExpectedTime);
            $stopPointsData->push($stopPointData);

            // Update previous expected time for sequential progression
            if ($arrival && $arrival->expectedArrivalTime) {
                $previousExpectedTime = \Carbon\Carbon::parse($arrival->expectedArrivalTime);
            } elseif ($stopPointData['calculatedExpectedTimeRaw']) {
                $previousExpectedTime = $stopPointData['calculatedExpectedTimeRaw'];
            }
        }

        // Auto-create arrivals for past times
        TripStopPointArrival::autoCreateForPastTimes($this, $stopPointsData, $arrivals);

        // Re-fetch arrivals after auto-creation
        $arrivals = TripStopPointArrival::where('driverParcelId', $this->parcelId)
            ->with(['stopPoint', 'approver'])
            ->get()
            ->keyBy('stopPointId');

        // Re-process with updated arrivals
        $previousExpectedTime = null;
        $stopPointsData = collect();

        foreach ($trip->stopPoints as $stopPoint) {
            $arrival = $arrivals->get($stopPoint->stopPointId);
            $stopPointData = $stopPoint->prepareForTripManagement($this, $arrival, $previousExpectedTime);
            $stopPointsData->push($stopPointData);

            // Update previous expected time
            if ($arrival && $arrival->expectedArrivalTime) {
                $previousExpectedTime = \Carbon\Carbon::parse($arrival->expectedArrivalTime);
            } elseif ($stopPointData['calculatedExpectedTimeRaw']) {
                $previousExpectedTime = $stopPointData['calculatedExpectedTimeRaw'];
            }
        }

        // Calculate completion status
        $allStopPointsCompleted = $stopPointsData->every(function ($stopPointData) {
            return $stopPointData['hasArrived'] ?? false;
        });

        // Determine current point
        $highestArrivalOrder = $arrivals
            ->filter(fn ($arrival) => in_array($arrival->status, ['approved', 'auto_approved']))
            ->map(fn ($arrival) => $arrival->stopPoint->order ?? 0)
            ->max() ?? 0;

        $stopPointsData = $stopPointsData->map(function ($stopPointData) use ($highestArrivalOrder) {
            $stopPointData['isCurrentPoint'] = ! ($stopPointData['hasArrived'] ?? false) &&
                                               ($stopPointData['order'] ?? 0) === $highestArrivalOrder + 1;

            return $stopPointData;
        });

        return [
            'stopPoints' => $stopPointsData->values()->all(),
            'isCompleted' => $allStopPointsCompleted && $this->status === 'arrived',
            'isActive' => $this->status === 'in_transit',
        ];
    }

    /**
     * Auto-update status based on tripDate.
     */
    public function updateStatusBasedOnTripDate(): void
    {
        if (! $this->tripDate) {
            return;
        }

        $tripDate = \Carbon\Carbon::parse($this->tripDate);
        $today = \Carbon\Carbon::today();

        if ($tripDate->isFuture()) {
            if ($this->status === 'pending') {
                self::where('parcelId', $this->parcelId)->update(['status' => 'not_started']);
                $this->status = 'not_started';
            }
        } elseif (($tripDate->isSameDay($today) || $tripDate->isPast()) && $this->status === 'not_started') {
            self::where('parcelId', $this->parcelId)->update(['status' => 'pending']);
            $this->status = 'pending';
        }
    }

    /**
     * Get trip management statistics - OPTIMIZED with single query.
     */
    public static function getTripManagementStatistics(): array
    {
        $stats = self::whereNotNull('tripId')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status IN ("in_transit", "arrived") THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = "arrived" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "not_started" THEN 1 ELSE 0 END) as not_started
            ')
            ->first();

        return [
            'total' => (int) ($stats->total ?? 0),
            'active' => (int) ($stats->active ?? 0),
            'completed' => (int) ($stats->completed ?? 0),
            'not_started' => (int) ($stats->not_started ?? 0),
        ];
    }

    /**
     * Get trip management data - ULTRA FAST version.
     * Minimal queries, minimal processing, maximum speed.
     */
    public static function getTripManagementDataOptimized(string $filter = 'all', int $limit = 20): array
    {
        // Get statistics first (cached)
        $stats = \Illuminate\Support\Facades\Cache::remember(
            'trip_management_stats',
            60,
            fn () => self::getTripManagementStatistics()
        );

        // Build minimal query - only essential fields
        $query = self::select([
            'parcelId', 'parcelNumber', 'driverName', 'tripId', 'tripDate', 'parcelDate',
            'status', 'officeId',
        ])
            ->whereNotNull('tripId')
            ->orderBy('tripDate', 'desc')
            ->orderBy('parcelDate', 'desc')
            ->limit($limit);

        // Apply filter
        if ($filter === 'active') {
            $query->whereIn('status', ['in_transit', 'arrived']);
        } elseif ($filter === 'completed') {
            $query->where('status', 'arrived');
        } elseif ($filter === 'not_started') {
            $query->where('status', 'not_started');
        }

        // Load driver parcels
        $driverParcels = $query->get();

        if ($driverParcels->isEmpty()) {
            return [
                'driverParcels' => [],
                'stats' => $stats,
            ];
        }

        // Get trip IDs and office IDs
        $tripIds = $driverParcels->pluck('tripId')->unique()->filter()->toArray();
        $officeIds = $driverParcels->pluck('officeId')->unique()->filter()->toArray();
        $parcelIds = $driverParcels->pluck('parcelId')->toArray();

        // Load trips with stop points in ONE query
        $trips = \App\Models\Trip::whereIn('tripId', $tripIds)
            ->with(['stopPoints' => fn ($q) => $q->orderBy('order')->select(['stopPointId', 'tripId', 'stopName', 'arrivalTime', 'order'])])
            ->get()
            ->keyBy('tripId');

        // Load offices in ONE query
        $offices = \App\Models\Office::whereIn('officeId', $officeIds)
            ->pluck('officeName', 'officeId');

        // Load ALL arrivals in ONE query - minimal fields
        $allArrivals = TripStopPointArrival::whereIn('driverParcelId', $parcelIds)
            ->select([
                'arrivalId', 'driverParcelId', 'stopPointId', 'arrivedAt', 'expectedArrivalTime',
                'status', 'onTime', 'delayReason', 'delayDuration', 'adminComment', 'approvedAt',
            ])
            ->get()
            ->groupBy('driverParcelId')
            ->map(fn ($arrivals) => $arrivals->keyBy('stopPointId'));

        // Process data - SIMPLIFIED
        $driverParcelsData = [];
        foreach ($driverParcels as $dp) {
            $trip = $trips->get($dp->tripId);
            if (! $trip || ! $trip->stopPoints) {
                continue;
            }

            $arrivals = $allArrivals->get($dp->parcelId, collect());
            $stopPointsData = [];

            foreach ($trip->stopPoints as $sp) {
                $arrival = $arrivals->get($sp->stopPointId);
                $hasArrived = $arrival && in_array($arrival->status, ['approved', 'auto_approved']);

                // Simple expected time calculation
                $expectedTime = null;
                if ($arrival && $arrival->expectedArrivalTime) {
                    $expectedTime = $arrival->expectedArrivalTime->format('Y-m-d H:i');
                } elseif ($dp->tripDate && $sp->arrivalTime) {
                    $tripDate = is_string($dp->tripDate) ? \Carbon\Carbon::parse($dp->tripDate) : $dp->tripDate;
                    if (is_string($sp->arrivalTime) && preg_match('/^\d{1,2}:\d{2}/', $sp->arrivalTime)) {
                        [$h, $m] = explode(':', $sp->arrivalTime);
                        $expectedTime = $tripDate->copy()->setTime((int) $h, (int) $m)->format('Y-m-d H:i');
                    }
                }

                $arrivalData = null;
                if ($arrival) {
                    $arrivalData = [
                        'arrivalId' => $arrival->arrivalId,
                        'arrivedAt' => $arrival->arrivedAt ? (is_string($arrival->arrivedAt) ? $arrival->arrivedAt : $arrival->arrivedAt->format('Y-m-d H:i')) : null,
                        'expectedArrivalTime' => $arrival->expectedArrivalTime ? (is_string($arrival->expectedArrivalTime) ? $arrival->expectedArrivalTime : $arrival->expectedArrivalTime->format('Y-m-d H:i')) : null,
                        'onTime' => $arrival->onTime,
                        'onTimeText' => $arrival->onTime === true ? 'في الوقت المحدد' : ($arrival->onTime === false ? 'تأخر' : null),
                        'delayReason' => $arrival->delayReason,
                        'delayDuration' => $arrival->delayDuration,
                        'adminComment' => $arrival->adminComment,
                        'approvedAt' => $arrival->approvedAt ? (is_string($arrival->approvedAt) ? $arrival->approvedAt : $arrival->approvedAt->format('Y-m-d H:i')) : null,
                    ];
                }

                $stopPointsData[] = [
                    'stopPointId' => $sp->stopPointId,
                    'stopName' => $sp->stopName,
                    'order' => $sp->order,
                    'hasArrived' => $hasArrived,
                    'isFirstPoint' => $sp->order === 1,
                    'hasDelay' => $arrival && $arrival->delayReason && $arrival->delayDuration,
                    'shouldShowMarkArrivedButton' => ! $hasArrived && $expectedTime && \Carbon\Carbon::parse($expectedTime)->addMinute()->isPast(),
                    'calculatedExpectedTime' => $expectedTime,
                    'arrival' => $arrivalData,
                ];
            }

            // Simple completion check
            $allCompleted = collect($stopPointsData)->every(fn ($sp) => $sp['hasArrived']);

            // Prepare minimal view data
            $tripDate = is_string($dp->tripDate) ? \Carbon\Carbon::parse($dp->tripDate) : $dp->tripDate;
            $parcelDate = $dp->parcelDate ? (is_string($dp->parcelDate) ? \Carbon\Carbon::parse($dp->parcelDate) : $dp->parcelDate) : null;

            $driverParcelsData[] = [
                'parcelId' => $dp->parcelId,
                'parcelNumber' => $dp->parcelNumber,
                'driverName' => $dp->driverName,
                'officeName' => $offices->get($dp->officeId, 'غير محدد'),
                'tripName' => $trip->tripName ?? null,
                'showTripName' => ! empty($trip->tripName),
                'tripDate' => $tripDate?->format('Y-m-d'),
                'showTripDate' => ! empty($dp->tripDate),
                'parcelDate' => $parcelDate?->format('Y-m-d'),
                'showParcelDate' => empty($dp->tripDate) && ! empty($dp->parcelDate),
                'status' => $dp->status,
                'statusText' => self::getStatusTextStatic($dp->status),
                'statusBadge' => self::getStatusBadgeDataStatic($dp->status, $allCompleted && $dp->status === 'arrived'),
                'isCompleted' => $allCompleted && $dp->status === 'arrived',
                'isActive' => $dp->status === 'in_transit',
                'stopPoints' => self::prepareStopPointsForView($stopPointsData, $dp->parcelId),
            ];
        }

        return [
            'driverParcels' => $driverParcelsData,
            'stats' => $stats,
        ];
    }

    /**
     * Prepare stop points for view - minimal processing.
     */
    protected static function prepareStopPointsForView(array $stopPointsData, int $parcelId): array
    {
        foreach ($stopPointsData as &$sp) {
            $hasArrived = $sp['hasArrived'];
            $arrival = $sp['arrival'];

            $expectedTimeForModal = $arrival['expectedArrivalTime'] ?? $sp['calculatedExpectedTime'] ?? null;
            $sp['modalId'] = $hasArrived && $arrival
                ? 'editModal'.$arrival['arrivalId']
                : 'editModal'.$parcelId.'_'.$sp['stopPointId'];
            $sp['expectedTimeForModal'] = $expectedTimeForModal ? str_replace(' ', 'T', substr($expectedTimeForModal, 0, 16)) : '';
            $sp['showArrivedAt'] = $hasArrived && ! empty($arrival['arrivedAt']);
            $sp['showOnTime'] = $hasArrived && ! empty($arrival['onTimeText']);
            $sp['showAutoApprovedMessage'] = $sp['isFirstPoint'] && $hasArrived;
            $sp['showDelay'] = $sp['hasDelay'];
            $sp['showAdminComment'] = $hasArrived && ! empty($arrival['adminComment']);
            $sp['showApprovedAt'] = $hasArrived && ! empty($arrival['approvedAt']);
            $sp['showExpectedTime'] = ! empty($arrival['expectedArrivalTime']) || ! empty($sp['calculatedExpectedTime']);
            $sp['expectedTimeDisplay'] = $arrival['expectedArrivalTime'] ?? $sp['calculatedExpectedTime'] ?? null;
            $sp['expectedTimeLabel'] = ! empty($arrival['expectedArrivalTime']) ? 'متوقع (محدث)' : 'متوقع';
            $sp['expectedTimeIcon'] = ! empty($arrival['expectedArrivalTime']) ? 'fa-calendar-alt' : 'fa-clock';
            $sp['showDelayModal'] = $hasArrived && $arrival;
            $sp['showEditModal'] = true;
        }

        return $stopPointsData;
    }

    /**
     * Batch update statuses based on tripDate - single query instead of per-parcel.
     */
    protected static function batchUpdateStatusesBasedOnTripDate($driverParcels): void
    {
        $today = \Carbon\Carbon::today();
        $updates = ['not_started' => [], 'pending' => []];

        foreach ($driverParcels as $dp) {
            if (! $dp->tripDate) {
                continue;
            }

            $tripDate = \Carbon\Carbon::parse($dp->tripDate);
            if ($tripDate->isFuture() && $dp->status === 'pending') {
                $updates['not_started'][] = $dp->parcelId;
            } elseif (($tripDate->isSameDay($today) || $tripDate->isPast()) && $dp->status === 'not_started') {
                $updates['pending'][] = $dp->parcelId;
            }
        }

        // Batch update
        if (! empty($updates['not_started'])) {
            self::whereIn('parcelId', $updates['not_started'])->update(['status' => 'not_started']);
            foreach ($updates['not_started'] as $id) {
                $driverParcels->firstWhere('parcelId', $id)->status = 'not_started';
            }
        }
        if (! empty($updates['pending'])) {
            self::whereIn('parcelId', $updates['pending'])->update(['status' => 'pending']);
            foreach ($updates['pending'] as $id) {
                $driverParcels->firstWhere('parcelId', $id)->status = 'pending';
            }
        }
    }

    /**
     * Process stop points optimized - single pass, no double processing.
     */
    protected static function processStopPointsOptimized($stopPoints, self $driverParcel, $arrivals): \Illuminate\Support\Collection
    {
        $previousExpectedTime = null;
        $stopPointsData = collect();

        foreach ($stopPoints as $stopPoint) {
            $arrival = $arrivals->get($stopPoint->stopPointId);
            $stopPointData = $stopPoint->prepareForTripManagement($driverParcel, $arrival, $previousExpectedTime);
            $stopPointsData->push($stopPointData);

            // Update previous expected time
            if ($arrival && $arrival->expectedArrivalTime) {
                $previousExpectedTime = \Carbon\Carbon::parse($arrival->expectedArrivalTime);
            } elseif ($stopPointData['calculatedExpectedTimeRaw']) {
                $previousExpectedTime = $stopPointData['calculatedExpectedTimeRaw'];
            }
        }

        return $stopPointsData;
    }

    /**
     * Auto-create missing arrivals - returns collection of new arrivals to batch insert.
     */
    protected static function autoCreateMissingArrivals(self $driverParcel, $stopPointsData, $arrivals): \Illuminate\Support\Collection
    {
        $trip = $driverParcel->trip;
        if (! $trip) {
            return collect();
        }

        $newArrivals = collect();
        $previousExpectedTime = null;

        foreach ($stopPointsData as $spData) {
            if ($spData['hasArrived'] ?? false) {
                $arrival = $arrivals->get($spData['stopPointId']);
                if ($arrival && $arrival->expectedArrivalTime) {
                    $previousExpectedTime = \Carbon\Carbon::parse($arrival->expectedArrivalTime);
                }

                continue;
            }

            $expectedTime = $spData['calculatedExpectedTimeRaw'] ?? null;
            if (! $expectedTime || ! $expectedTime->copy()->addMinute()->isPast()) {
                $previousExpectedTime = $expectedTime;

                continue;
            }

            // Check sequential progression
            $canAutoCreate = true;
            if (($spData['order'] ?? 0) > 1) {
                $prevStopPoints = $trip->stopPoints->where('order', '<', $spData['order']);
                foreach ($prevStopPoints as $prevStopPoint) {
                    $prevArrival = $arrivals->get($prevStopPoint->stopPointId);
                    if (! $prevArrival) {
                        $prevExpectedTime = $prevStopPoint->calculateExpectedTimeForDriverParcel($driverParcel, $previousExpectedTime);
                        if ($prevExpectedTime && $prevExpectedTime->isPast()) {
                            // Create previous arrival
                            $prevArrival = TripStopPointArrival::createAutoArrival(
                                $driverParcel->parcelId,
                                $prevStopPoint->stopPointId,
                                $prevExpectedTime
                            );
                            if ($prevArrival) {
                                $arrivals->put($prevStopPoint->stopPointId, $prevArrival);
                                $newArrivals->put($prevStopPoint->stopPointId, $prevArrival);
                                $previousExpectedTime = $prevExpectedTime;
                            }
                        } else {
                            $canAutoCreate = false;
                            break;
                        }
                    } elseif (! in_array($prevArrival->status, ['approved', 'auto_approved'])) {
                        $canAutoCreate = false;
                        break;
                    } else {
                        $previousExpectedTime = $prevArrival->expectedArrivalTime ?? $previousExpectedTime;
                    }
                }
            }

            if ($canAutoCreate) {
                $arrival = TripStopPointArrival::createAutoArrival(
                    $driverParcel->parcelId,
                    $spData['stopPointId'],
                    $expectedTime
                );
                if ($arrival) {
                    $newArrivals->put($spData['stopPointId'], $arrival);
                    $previousExpectedTime = $expectedTime;
                }
            }
        }

        return $newArrivals;
    }

    /**
     * Prepare driver parcel data for view.
     */
    protected static function prepareDriverParcelForView(self $driverParcel, $stopPointsData, bool $isCompleted): array
    {
        $statusBadge = self::getStatusBadgeDataStatic($driverParcel->status, $isCompleted);

        $stopPoints = $stopPointsData->map(function ($stopPoint) use ($driverParcel) {
            $hasArrived = $stopPoint['hasArrived'] ?? false;
            $arrival = $stopPoint['arrival'] ?? null;

            $expectedTimeForModal = $arrival['expectedArrivalTime'] ?? $stopPoint['calculatedExpectedTime'] ?? null;
            $expectedTimeForModalFormatted = $expectedTimeForModal ? str_replace(' ', 'T', substr($expectedTimeForModal, 0, 16)) : '';

            return array_merge($stopPoint, [
                'modalId' => $hasArrived && $arrival
                    ? 'editModal'.$arrival['arrivalId']
                    : 'editModal'.$driverParcel->parcelId.'_'.$stopPoint['stopPointId'],
                'expectedTimeForModal' => $expectedTimeForModalFormatted,
                'showArrivedAt' => $hasArrived && ! empty($arrival['arrivedAt']),
                'showOnTime' => $hasArrived && ! empty($arrival['onTimeText']),
                'showAutoApprovedMessage' => ($stopPoint['isFirstPoint'] ?? false) && $hasArrived,
                'showDelay' => $stopPoint['hasDelay'] ?? false,
                'showAdminComment' => $hasArrived && ! empty($arrival['adminComment']),
                'showApprovedAt' => $hasArrived && ! empty($arrival['approvedAt']),
                'showExpectedTime' => ! empty($arrival['expectedArrivalTime']) || ! empty($stopPoint['calculatedExpectedTime']),
                'expectedTimeDisplay' => $arrival['expectedArrivalTime'] ?? $stopPoint['calculatedExpectedTime'] ?? null,
                'expectedTimeLabel' => ! empty($arrival['expectedArrivalTime']) ? 'متوقع (محدث)' : 'متوقع',
                'expectedTimeIcon' => ! empty($arrival['expectedArrivalTime']) ? 'fa-calendar-alt' : 'fa-clock',
                'showDelayModal' => $hasArrived && $arrival,
                'showEditModal' => true,
            ]);
        })->all();

        return [
            'parcelId' => $driverParcel->parcelId,
            'parcelNumber' => $driverParcel->parcelNumber,
            'driverName' => $driverParcel->driverName,
            'officeName' => $driverParcel->office->officeName ?? 'غير محدد',
            'tripName' => $driverParcel->trip->tripName ?? null,
            'showTripName' => ! empty($driverParcel->trip->tripName),
            'tripDate' => $driverParcel->tripDate ? (is_string($driverParcel->tripDate) ? \Carbon\Carbon::parse($driverParcel->tripDate)->format('Y-m-d') : ($driverParcel->tripDate instanceof \Carbon\Carbon ? $driverParcel->tripDate->format('Y-m-d') : \Carbon\Carbon::parse($driverParcel->tripDate)->format('Y-m-d'))) : null,
            'showTripDate' => ! empty($driverParcel->tripDate),
            'parcelDate' => $driverParcel->parcelDate ? (is_string($driverParcel->parcelDate) ? \Carbon\Carbon::parse($driverParcel->parcelDate)->format('Y-m-d') : \Carbon\Carbon::parse($driverParcel->parcelDate)->format('Y-m-d')) : null,
            'showParcelDate' => empty($driverParcel->tripDate) && ! empty($driverParcel->parcelDate),
            'status' => $driverParcel->status,
            'statusText' => self::getStatusTextStatic($driverParcel->status),
            'statusBadge' => $statusBadge,
            'isCompleted' => $isCompleted && $driverParcel->status === 'arrived',
            'isActive' => $driverParcel->status === 'in_transit',
            'stopPoints' => $stopPoints,
        ];
    }

    /**
     * Get status text in Arabic (static version).
     */
    protected static function getStatusTextStatic(string $status): string
    {
        return match ($status) {
            'in_transit' => 'قيد النقل',
            'arrived' => 'وصلت',
            'not_started' => 'لم تبدأ',
            'delivered' => 'تم التسليم',
            default => $status,
        };
    }

    /**
     * Get status badge data (static version).
     */
    protected static function getStatusBadgeDataStatic(string $status, bool $isCompleted): array
    {
        if ($status === 'not_started') {
            return ['class' => 'badge-secondary', 'icon' => 'fa-clock', 'text' => 'لم تبدأ'];
        }
        if ($isCompleted) {
            return ['class' => 'badge-success', 'icon' => 'fa-check-circle', 'text' => 'مكتملة'];
        }
        if ($status === 'in_transit') {
            return ['class' => 'badge-info', 'icon' => 'fa-sync-alt', 'text' => 'قيد النقل'];
        }
        if ($status === 'arrived') {
            return ['class' => 'badge-success', 'icon' => 'fa-check', 'text' => 'وصلت'];
        }

        return ['class' => 'badge-info', 'icon' => 'fa-sync-alt', 'text' => 'نشطة'];
    }
}
