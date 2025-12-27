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

    /**
     * Calculate expected arrival time for a driver parcel.
     */
    public function calculateExpectedTimeForDriverParcel(DriverParcel $driverParcel, ?\Carbon\Carbon $previousExpectedTime = null): ?\Carbon\Carbon
    {
        if (! $driverParcel->tripDate || ! $this->arrivalTime) {
            return null;
        }

        $tripDate = \Carbon\Carbon::parse($driverParcel->tripDate);

        // Handle arrivalTime - it might be a time string (HH:mm) or datetime
        if (is_string($this->arrivalTime) && preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $this->arrivalTime)) {
            $timeParts = explode(':', $this->arrivalTime);
            $hour = (int) ($timeParts[0] ?? 0);
            $minute = (int) ($timeParts[1] ?? 0);
            $calculatedTime = $tripDate->copy()->setTime($hour, $minute, 0);
        } else {
            $stopTime = \Carbon\Carbon::parse($this->arrivalTime);
            $calculatedTime = $tripDate->copy()->setTime($stopTime->hour, $stopTime->minute, $stopTime->second);
        }

        // Ensure sequential progression: if this time is before previous point, use previous point's time + 1 hour
        if ($previousExpectedTime && $calculatedTime->lt($previousExpectedTime)) {
            $calculatedTime = $previousExpectedTime->copy()->addHour();
        }

        return $calculatedTime;
    }

    /**
     * Prepare stop point data for trip management view.
     */
    public function prepareForTripManagement(DriverParcel $driverParcel, ?TripStopPointArrival $arrival = null, ?\Carbon\Carbon $previousExpectedTime = null): array
    {
        // Calculate expected time
        $calculatedTime = $this->calculateExpectedTimeForDriverParcel($driverParcel, $previousExpectedTime);

        // If arrival has expectedArrivalTime, use that (it might have been edited)
        if ($arrival && $arrival->expectedArrivalTime) {
            $calculatedTime = \Carbon\Carbon::parse($arrival->expectedArrivalTime);
            // Still ensure it's not before previous point
            if ($previousExpectedTime && $calculatedTime->lt($previousExpectedTime)) {
                $calculatedTime = $previousExpectedTime->copy()->addHour();
            }
        }

        $hasArrived = $arrival && in_array($arrival->status, ['approved', 'auto_approved']);
        $hasDelay = $arrival && $arrival->delayReason && $arrival->delayDuration;
        $isFirstPoint = $this->order === 1;

        // Check if it's time to show "Mark Arrived" button
        $shouldShowMarkArrivedButton = ! $hasArrived && $calculatedTime && $calculatedTime->copy()->addMinute()->isPast();

        // Prepare all data for view
        return [
            'stopPointId' => $this->stopPointId,
            'stopName' => $this->stopName,
            'order' => $this->order,
            'arrivalTime' => $this->arrivalTime,
            'calculatedExpectedTime' => $calculatedTime?->format('Y-m-d H:i'),
            'calculatedExpectedTimeRaw' => $calculatedTime,
            'hasArrived' => $hasArrived,
            'hasDelay' => $hasDelay,
            'isFirstPoint' => $isFirstPoint,
            'shouldShowMarkArrivedButton' => $shouldShowMarkArrivedButton,
            'arrival' => $arrival ? [
                'arrivalId' => $arrival->arrivalId,
                'arrivedAt' => $arrival->arrivedAt?->format('Y-m-d H:i'),
                'expectedArrivalTime' => $arrival->expectedArrivalTime?->format('Y-m-d H:i'),
                'expectedArrivalTimeRaw' => $arrival->expectedArrivalTime,
                'onTime' => $arrival->onTime,
                'onTimeText' => $arrival->onTime === true ? 'في الوقت المحدد' : ($arrival->onTime === false ? 'تأخر' : null),
                'delayReason' => $arrival->delayReason,
                'delayDuration' => $arrival->delayDuration,
                'adminComment' => $arrival->adminComment,
                'approvedAt' => $arrival->approvedAt?->format('Y-m-d H:i'),
                'status' => $arrival->status,
            ] : null,
        ];
    }
}
