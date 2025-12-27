<?php

namespace App\Observers;

use App\Models\TripStopPointArrival;

class TripStopPointArrivalObserver
{
    /**
     * Handle the TripStopPointArrival "created" event.
     */
    public function created(TripStopPointArrival $arrival): void
    {
        if ($arrival->driverParcel && $arrival->stopPoint && $arrival->driverParcel->tripId) {
            // Get the stop point order
            $stopPointOrder = $arrival->stopPoint->order ?? 0;

            // If arrival is already auto_approved, ensure it has all required fields
            if ($arrival->status === 'auto_approved') {
                $updateData = [];

                // Ensure arrivedAt is set
                if (! $arrival->arrivedAt) {
                    $updateData['arrivedAt'] = $arrival->expectedArrivalTime ?? now();
                }

                // Ensure approvedAt is set
                if (! $arrival->approvedAt) {
                    $updateData['approvedAt'] = now();
                }

                // Ensure onTime is set (default to true if not set)
                if ($arrival->onTime === null) {
                    $onTime = true;
                    if ($arrival->expectedArrivalTime && $arrival->arrivedAt) {
                        $onTime = $arrival->arrivedAt->lte($arrival->expectedArrivalTime->copy()->addMinutes(5));
                    }
                    $updateData['onTime'] = $onTime;
                }

                // Special message for first point
                if ($stopPointOrder === 1 && ! $arrival->adminComment) {
                    $updateData['adminComment'] = 'تمت الموافقة التلقائية - نقطة البداية';
                }

                if (! empty($updateData)) {
                    $arrival->update($updateData);
                }
            }

            // Auto-approve all previous pending stop points when driver arrives at a later point
            if ($stopPointOrder > 1) {
                TripStopPointArrival::autoApprovePreviousPoints(
                    $arrival->driverParcelId,
                    $arrival->stopPointId,
                    $arrival->driverParcel->tripId
                );
            }

            // Create tracking record
            $arrival->createTrackingForAutoApproval();
        }
    }

    /**
     * Handle the TripStopPointArrival "updated" event.
     */
    public function updated(TripStopPointArrival $arrival): void
    {
        // No special handling needed for updates
        // Tracking and other updates are handled in the model or controller
    }
}
