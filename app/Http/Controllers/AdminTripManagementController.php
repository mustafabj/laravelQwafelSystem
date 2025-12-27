<?php

namespace App\Http\Controllers;

use App\Models\DriverParcel;
use App\Models\Trip;
use App\Models\TripStopPointArrival;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminTripManagementController extends Controller
{
    /**
     * Display admin trip management page.
     */
    public function index(Request $request): View
    {
        $filter = $request->get('filter', 'all');

        // Use ultra-fast method - limit to 20 for maximum speed
        $result = DriverParcel::getTripManagementDataOptimized($filter, 20);

        return view('admin.trip-management.index', [
            'driverParcels' => $result['driverParcels'],
            'filter' => $filter,
            'stats' => $result['stats'],
        ]);
    }

    /**
     * Get status text in Arabic.
     */
    private function getStatusText(string $status): string
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
     * Get status badge data for view.
     */
    private function getStatusBadgeData(string $status, bool $isCompleted): array
    {
        if ($status === 'not_started') {
            return [
                'class' => 'badge-secondary',
                'icon' => 'fa-clock',
                'text' => 'لم تبدأ',
            ];
        }

        if ($isCompleted) {
            return [
                'class' => 'badge-success',
                'icon' => 'fa-check-circle',
                'text' => 'مكتملة',
            ];
        }

        if ($status === 'in_transit') {
            return [
                'class' => 'badge-info',
                'icon' => 'fa-sync-alt',
                'text' => 'قيد النقل',
            ];
        }

        if ($status === 'arrived') {
            return [
                'class' => 'badge-success',
                'icon' => 'fa-check',
                'text' => 'وصلت',
            ];
        }

        return [
            'class' => 'badge-info',
            'icon' => 'fa-sync-alt',
            'text' => 'نشطة',
        ];
    }

    /**
     * Get pending arrivals for a specific trip.
     */
    public function getTripArrivals(int $tripId): JsonResponse
    {
        $arrivals = TripStopPointArrival::with([
            'driverParcel',
            'stopPoint',
            'driverParcel.details.parcelDetail.parcel.customer',
        ])
            ->whereHas('driverParcel', function ($query) use ($tripId) {
                $query->where('tripId', $tripId);
            })
            ->where('status', 'pending')
            ->orderBy('requestedAt', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'arrivals' => $arrivals,
        ]);
    }

    /**
     * Approve arrival with delay and update subsequent stop points.
     */
    public function approveWithDelay(Request $request, int $arrivalId): JsonResponse
    {
        $request->validate([
            'delayReason' => 'required|string|max:1000',
            'delayDuration' => 'required|integer|min:1',
            'comment' => 'nullable|string|max:1000',
        ]);

        $arrival = TripStopPointArrival::findOrFail($arrivalId);

        // Can record delay on any arrived point
        if ($arrival->status !== 'approved' && $arrival->status !== 'auto_approved') {
            return response()->json([
                'success' => false,
                'message' => 'يجب أن تكون النقطة قد وصلت لتسجيل التأخير',
            ], 422);
        }

        $user = Auth::user();

        // Record delay on the arrival
        $arrival->recordDelay($request->delayReason, $request->delayDuration, $request->comment);

        // Create tracking record for customer
        $this->createTrackingForStopPoint($arrival);

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل التأخير بنجاح. تم تحديث أوقات النقاط اللاحقة.',
        ]);
    }

    /**
     * Create tracking record for stop point arrival.
     */
    protected function createTrackingForStopPoint(TripStopPointArrival $arrival): void
    {
        $driverParcel = $arrival->driverParcel;
        $stopPoint = $arrival->stopPoint;

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

        $status = $arrival->status === 'approved' || $arrival->status === 'auto_approved' ? 'in_transit' : 'pending';
        $description = "وصل السائق إلى نقطة: {$stopPoint->stopName}";

        if ($arrival->delayReason && $arrival->delayDuration) {
            $description .= " - تأخير: {$arrival->delayReason} ({$arrival->delayDuration} دقيقة)";
        }

        if ($arrival->adminComment) {
            $description .= " - {$arrival->adminComment}";
        }

        if ($arrival->onTime === false) {
            $description .= ' (تأخر)';
        } elseif ($arrival->onTime === true) {
            $description .= ' (في الوقت المحدد)';
        }

        // Create tracking for each parcel
        foreach ($parcelIds as $parcelId) {
            \App\Models\ParcelTracking::createTracking(
                $parcelId,
                $driverParcel->parcelId,
                $driverParcel->tripId,
                $status,
                $stopPoint->stopName,
                $description,
                $arrival->approver ? $arrival->approver->name : 'system',
                null
            );
        }
    }

    /**
     * Get arrival details.
     */
    public function getArrival(int $arrivalId): JsonResponse
    {
        $arrival = TripStopPointArrival::with([
            'driverParcel.trip',
            'driverParcel.office',
            'stopPoint',
            'driverParcel.details.parcelDetail.parcel.customer',
            'approver',
        ])->findOrFail($arrivalId);

        return response()->json([
            'success' => true,
            'arrival' => $arrival,
        ]);
    }

    /**
     * Update arrival time and notes.
     */
    public function updateArrival(Request $request, ?int $arrivalId = null): JsonResponse
    {
        $request->validate([
            'expectedArrivalTime' => 'nullable|date',
            'adminComment' => 'nullable|string|max:1000',
            'driverParcelId' => 'nullable|integer',
            'stopPointId' => 'nullable|integer',
        ]);

        $arrival = null;

        // Handle editing non-arrived points (create arrival record if doesn't exist)
        // If arrivalId is null or 0, and we have driverParcelId and stopPointId, create new record
        if ((! $arrivalId || $arrivalId === 0) && $request->driverParcelId && $request->stopPointId) {
            // Try to find existing arrival first
            $arrival = TripStopPointArrival::where('driverParcelId', $request->driverParcelId)
                ->where('stopPointId', $request->stopPointId)
                ->first();

            if (! $arrival) {
                // Create new arrival record for editing
                $driverParcel = DriverParcel::findOrFail($request->driverParcelId);
                $stopPoint = \App\Models\TripStopPoint::findOrFail($request->stopPointId);

                $expectedArrivalTime = null;
                if ($request->expectedArrivalTime) {
                    $expectedArrivalTime = \Carbon\Carbon::parse($request->expectedArrivalTime);
                } elseif ($driverParcel->tripDate && $stopPoint->arrivalTime) {
                    // Calculate from tripDate + stop point time
                    $tripDate = \Carbon\Carbon::parse($driverParcel->tripDate);
                    if (is_string($stopPoint->arrivalTime) && preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $stopPoint->arrivalTime)) {
                        $timeParts = explode(':', $stopPoint->arrivalTime);
                        $hour = (int) ($timeParts[0] ?? 0);
                        $minute = (int) ($timeParts[1] ?? 0);
                        $expectedArrivalTime = $tripDate->copy()->setTime($hour, $minute, 0);
                    } else {
                        $stopTime = \Carbon\Carbon::parse($stopPoint->arrivalTime);
                        $expectedArrivalTime = $tripDate->copy()->setTime($stopTime->hour, $stopTime->minute, $stopTime->second);
                    }
                }

                // Determine if on time
                $onTime = true;
                if ($expectedArrivalTime && $expectedArrivalTime->isPast()) {
                    $onTime = $expectedArrivalTime->lte($expectedArrivalTime->copy()->addMinutes(5));
                }

                $arrival = TripStopPointArrival::create([
                    'driverParcelId' => $request->driverParcelId,
                    'stopPointId' => $request->stopPointId,
                    'expectedArrivalTime' => $expectedArrivalTime,
                    'adminComment' => $request->adminComment,
                    'status' => 'auto_approved',
                    'onTime' => $onTime,
                    'approvedAt' => now(),
                    'arrivedAt' => $expectedArrivalTime ?? now(),
                ]);
            }
        } elseif ($arrivalId && $arrivalId > 0) {
            // Find existing arrival by ID
            $arrival = TripStopPointArrival::findOrFail($arrivalId);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'يجب توفير معرف الوصول أو معرف الإرسالية ونقطة التوقف',
            ], 422);
        }

        $updateData = [];
        if ($request->has('expectedArrivalTime') && $request->expectedArrivalTime) {
            $updateData['expectedArrivalTime'] = \Carbon\Carbon::parse($request->expectedArrivalTime);
        }
        if ($request->has('adminComment')) {
            $updateData['adminComment'] = $request->adminComment;
        }

        if (! empty($updateData)) {
            $arrival->update($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المعلومات بنجاح',
            'arrival' => $arrival->fresh(['driverParcel.trip', 'stopPoint', 'approver']),
        ]);
    }

    /**
     * Mark driver as arrived at a stop point.
     */
    public function markArrived(Request $request, int $driverParcelId, int $stopPointId): JsonResponse
    {
        $request->validate([
            'arrivedAt' => 'nullable|date',
        ]);

        $driverParcel = DriverParcel::findOrFail($driverParcelId);
        $stopPoint = \App\Models\TripStopPoint::findOrFail($stopPointId);

        // Check if arrival already exists
        $arrival = TripStopPointArrival::where('driverParcelId', $driverParcelId)
            ->where('stopPointId', $stopPointId)
            ->first();

        if ($arrival) {
            return response()->json([
                'success' => false,
                'message' => 'تم تسجيل الوصول إلى هذه النقطة مسبقاً',
            ], 422);
        }

        // Calculate expected arrival time based on tripDate + stop point time
        $expectedArrivalTime = null;
        if ($driverParcel->tripDate && $stopPoint->arrivalTime) {
            $tripDate = \Carbon\Carbon::parse($driverParcel->tripDate);
            $stopTime = \Carbon\Carbon::parse($stopPoint->arrivalTime);
            $expectedArrivalTime = $tripDate->copy()
                ->setTime($stopTime->hour, $stopTime->minute, $stopTime->second);
        }

        // Determine if on time
        $arrivedAt = $request->arrivedAt ? \Carbon\Carbon::parse($request->arrivedAt) : now();
        $onTime = true;
        if ($expectedArrivalTime) {
            $onTime = $arrivedAt->lte($expectedArrivalTime->copy()->addMinutes(5));
        }

        // Create arrival record directly as auto_approved
        $arrival = TripStopPointArrival::create([
            'driverParcelId' => $driverParcelId,
            'stopPointId' => $stopPointId,
            'arrivedAt' => $arrivedAt,
            'expectedArrivalTime' => $expectedArrivalTime,
            'status' => 'auto_approved',
            'onTime' => $onTime,
            'approvedAt' => now(),
        ]);

        // Create tracking record
        $arrival->createTrackingForAutoApproval();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الوصول إلى النقطة بنجاح',
            'arrival' => $arrival->fresh(['driverParcel.trip', 'stopPoint']),
        ]);
    }
}
