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

        return $this->requestedAt->addMinutes(15)->isPast();
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
            $onTime = $this->arrivedAt->lte($this->expectedArrivalTime->addMinutes(5)); // 5 min tolerance
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
     * Reject arrival by admin.
     */
    public function reject(int $userId, ?string $comment = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        return $this->update([
            'status' => 'rejected',
            'adminComment' => $comment,
            'approvedBy' => $userId,
            'approvedAt' => now(),
        ]);
    }
}
