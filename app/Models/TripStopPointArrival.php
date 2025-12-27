<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripStopPointArrival extends Model
{
    use HasFactory;

    protected $table = 'trip_stop_point_arrivals';

    protected $primaryKey = 'arrivalId';

    protected $fillable = [
        'driverParcelId',
        'stopPointId',
        'arrivedAt',
        'expectedArrivalTime',
        'status',
        'onTime',
        'adminComment',
        'delayReason',
        'delayDuration',
        'approvedBy',
        'approvedAt',
        'requestedAt',
    ];

    protected $casts = [
        'arrivalId' => 'integer',
        'driverParcelId' => 'integer',
        'stopPointId' => 'integer',
        'onTime' => 'boolean',
        'arrivedAt' => 'datetime',
        'expectedArrivalTime' => 'datetime',
        'approvedAt' => 'datetime',
        'requestedAt' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function driverParcel()
    {
        return $this->belongsTo(DriverParcel::class, 'driverParcelId', 'parcelId');
    }

    public function stopPoint()
    {
        return $this->belongsTo(TripStopPoint::class, 'stopPointId', 'stopPointId');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approvedBy', 'id');
    }

    /**
     * Check if arrival should be auto-approved (15 minutes passed).
     */
    public function shouldAutoApprove(): bool
    {
        if ($this->status !== 'pending' || ! $this->requestedAt) {
            return false;
        }

        return $this->requestedAt->copy()->addMinutes(15)->isPast();
    }

    /**
     * Auto-approve arrival.
     */
    public function autoApprove(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        // Determine if on time based on expected vs actual arrival
        $onTime = null;
        if ($this->expectedArrivalTime && $this->arrivedAt) {
            $onTime = $this->arrivedAt->lte($this->expectedArrivalTime->copy()->addMinutes(5)); // 5 min tolerance
        }

        return $this->update([
            'status' => 'auto_approved',
            'onTime' => $onTime,
            'approvedAt' => now(),
        ]);
    }

    /**
     * Approve arrival by admin.
     */
    public function approve(int $userId, bool $onTime, ?string $comment = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        return $this->update([
            'status' => 'approved',
            'onTime' => $onTime,
            'adminComment' => $comment,
            'approvedBy' => $userId,
            'approvedAt' => now(),
        ]);
    }

    /**
     * Record delay information for an already-arrived point and update subsequent stop points.
     */
    public function recordDelay(string $delayReason, int $delayDuration, ?string $comment = null): bool
    {
        // Can record delay on any arrived point
        if ($this->status !== 'approved' && $this->status !== 'auto_approved') {
            return false;
        }

        // Update with delay info
        $updated = $this->update([
            'onTime' => false, // Delayed, so not on time
            'delayReason' => $delayReason,
            'delayDuration' => $delayDuration,
            'adminComment' => $comment ?? $this->adminComment,
        ]);

        if ($updated && $this->driverParcel && $this->stopPoint && $this->driverParcel->tripId) {
            // Add delay duration to all subsequent stop points' expected arrival times
            $this->addDelayToSubsequentPoints($delayDuration);
        }

        return $updated;
    }

    /**
     * Add delay duration to all subsequent stop points' expected arrival times.
     */
    protected function addDelayToSubsequentPoints(int $delayMinutes): void
    {
        $currentStopPoint = $this->stopPoint;
        if (! $currentStopPoint) {
            return;
        }

        // Get all subsequent stop points (higher order)
        $subsequentStopPoints = \App\Models\TripStopPoint::where('tripId', $this->driverParcel->tripId)
            ->where('order', '>', $currentStopPoint->order)
            ->orderBy('order')
            ->pluck('stopPointId')
            ->toArray();

        if (empty($subsequentStopPoints)) {
            return;
        }

        // Update expected arrival times for all subsequent arrivals for this driver parcel
        $subsequentArrivals = self::where('driverParcelId', $this->driverParcelId)
            ->whereIn('stopPointId', $subsequentStopPoints)
            ->whereNotNull('expectedArrivalTime')
            ->get();

        foreach ($subsequentArrivals as $arrival) {
            $arrival->update([
                'expectedArrivalTime' => $arrival->expectedArrivalTime->copy()->addMinutes($delayMinutes),
            ]);
        }
    }

    /**
     * Auto-create arrival records for stop points where expected time has passed.
     */
    public static function autoCreateForPastTimes(DriverParcel $driverParcel, \Illuminate\Support\Collection $stopPointsData, \Illuminate\Support\Collection $existingArrivals): void
    {
        $trip = $driverParcel->trip;
        if (! $trip) {
            return;
        }

        $previousExpectedTime = null;

        foreach ($stopPointsData as $stopPointData) {
            // Skip if arrival already exists
            if ($stopPointData['hasArrived'] ?? false) {
                $arrival = $existingArrivals->get($stopPointData['stopPointId']);
                if ($arrival && $arrival->expectedArrivalTime) {
                    $previousExpectedTime = \Carbon\Carbon::parse($arrival->expectedArrivalTime);
                } else {
                    $previousExpectedTime = $stopPointData['calculatedExpectedTimeRaw'] ?? null;
                }

                continue;
            }

            // Skip if no expected time calculated
            $expectedTime = $stopPointData['calculatedExpectedTimeRaw'] ?? null;
            if (! $expectedTime) {
                continue;
            }

            // Check if expected time has passed (with 1 minute tolerance)
            if (! $expectedTime->copy()->addMinute()->isPast()) {
                $previousExpectedTime = $expectedTime;

                continue;
            }

            // Check sequential progression
            $canAutoCreate = true;
            if (($stopPointData['order'] ?? 0) > 1) {
                $previousStopPoints = $trip->stopPoints->where('order', '<', $stopPointData['order']);
                foreach ($previousStopPoints as $prevStopPoint) {
                    $prevArrival = $existingArrivals->get($prevStopPoint->stopPointId);
                    if (! $prevArrival) {
                        // Check if previous point's time has also passed
                        $prevExpectedTime = $prevStopPoint->calculateExpectedTimeForDriverParcel($driverParcel, $previousExpectedTime);
                        if ($prevExpectedTime && $prevExpectedTime->isPast()) {
                            // Auto-create previous point first
                            $prevArrival = self::createAutoArrival(
                                $driverParcel->parcelId,
                                $prevStopPoint->stopPointId,
                                $prevExpectedTime
                            );
                            if ($prevArrival) {
                                $existingArrivals->put($prevStopPoint->stopPointId, $prevArrival);
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
                $arrival = self::createAutoArrival(
                    $driverParcel->parcelId,
                    $stopPointData['stopPointId'],
                    $expectedTime
                );
                if ($arrival) {
                    $existingArrivals->put($stopPointData['stopPointId'], $arrival);
                    $previousExpectedTime = $expectedTime;
                }
            }
        }
    }

    /**
     * Create an auto-approved arrival record.
     */
    public static function createAutoArrival(int $driverParcelId, int $stopPointId, \Carbon\Carbon $expectedTime): ?self
    {
        // Check if already exists
        $existing = self::where('driverParcelId', $driverParcelId)
            ->where('stopPointId', $stopPointId)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Determine if on time
        $onTime = $expectedTime->lte($expectedTime->copy()->addMinutes(5));

        $arrival = self::create([
            'driverParcelId' => $driverParcelId,
            'stopPointId' => $stopPointId,
            'arrivedAt' => $expectedTime->copy(),
            'expectedArrivalTime' => $expectedTime,
            'status' => 'auto_approved',
            'onTime' => $onTime,
            'approvedAt' => now(),
        ]);

        // Create tracking record
        $arrival->createTrackingForAutoApproval();

        return $arrival->fresh();
    }

    /**
     * Auto-approve all previous pending stop points when driver arrives at a later point.
     * This ensures sequential progression - you can't be at point 3 if you haven't completed points 1 and 2.
     */
    public static function autoApprovePreviousPoints(int $driverParcelId, int $currentStopPointId, int $tripId): void
    {
        // Get the current stop point's order
        $currentStopPoint = \App\Models\TripStopPoint::where('stopPointId', $currentStopPointId)
            ->where('tripId', $tripId)
            ->first();

        if (! $currentStopPoint) {
            return;
        }

        // Get all stop points before the current one (lower order)
        $previousStopPoints = \App\Models\TripStopPoint::where('tripId', $tripId)
            ->where('order', '<', $currentStopPoint->order)
            ->orderBy('order')
            ->pluck('stopPointId')
            ->toArray();

        if (empty($previousStopPoints)) {
            return;
        }

        // Find all pending arrivals for previous stop points
        $previousPendingArrivals = self::where('driverParcelId', $driverParcelId)
            ->whereIn('stopPointId', $previousStopPoints)
            ->where('status', 'pending')
            ->get();

        // Auto-approve each previous pending arrival
        foreach ($previousPendingArrivals as $arrival) {
            // Determine if on time based on expected vs actual arrival
            $onTime = null;
            if ($arrival->expectedArrivalTime && $arrival->arrivedAt) {
                $onTime = $arrival->arrivedAt->lte($arrival->expectedArrivalTime->copy()->addMinutes(5)); // 5 min tolerance
            } elseif ($arrival->arrivedAt) {
                // If arrived but no expected time, assume on time
                $onTime = true;
            }

            $arrival->update([
                'status' => 'auto_approved',
                'onTime' => $onTime,
                'approvedAt' => now(),
                'adminComment' => $arrival->adminComment ?? 'تمت الموافقة التلقائية - السائق وصل إلى نقطة لاحقة',
            ]);

            // Create tracking record for auto-approved arrival
            $arrival->createTrackingForAutoApproval();
        }
    }

    /**
     * Create tracking record for auto-approved arrival (sequential progression or 15-minute timeout).
     */
    public function createTrackingForAutoApproval(): void
    {
        $driverParcel = $this->driverParcel;
        $stopPoint = $this->stopPoint;

        if (! $driverParcel || ! $stopPoint) {
            return;
        }

        // Get all parcels in this driver parcel
        $parcelIds = $driverParcel->details()
            ->with('parcelDetail.parcel')
            ->get()
            ->pluck('parcelDetail.parcel.parcelId')
            ->filter()
            ->unique()
            ->toArray();

        // Determine description based on context
        $isFirstPoint = $stopPoint->order === 1;
        if ($isFirstPoint) {
            $description = "وصل السائق إلى نقطة: {$stopPoint->stopName} (تمت الموافقة التلقائية)";
        } else {
            $description = "وصل السائق إلى نقطة: {$stopPoint->stopName} (موافقة تلقائية)";
        }

        if ($this->adminComment) {
            $description .= " - {$this->adminComment}";
        }

        if ($this->onTime === false) {
            $description .= ' (تأخر)';
        } elseif ($this->onTime === true) {
            $description .= ' (في الوقت المحدد)';
        }

        // Create tracking for each parcel
        foreach ($parcelIds as $parcelId) {
            \App\Models\ParcelTracking::createTracking(
                $parcelId,
                $driverParcel->parcelId,
                $driverParcel->tripId,
                'in_transit',
                $stopPoint->stopName,
                $description,
                'system',
                null
            );
        }
    }
}
