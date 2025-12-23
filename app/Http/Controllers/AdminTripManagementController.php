<?php

namespace App\Http\Controllers;

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
        $user = Auth::user();

        // Get trips with pending approvals
        $trips = Trip::with([
            'stopPoints',
            'driverParcels' => function ($query) {
                $query->whereIn('status', ['in_transit', 'arrived'])
                    ->with(['office', 'details.parcelDetail.parcel.customer']);
            },
        ])
            ->whereHas('driverParcels', function ($query) {
                $query->whereIn('status', ['in_transit', 'arrived']);
            })
            ->get();

        // Get all pending arrivals
        $pendingArrivals = TripStopPointArrival::with([
            'driverParcel.trip',
            'driverParcel.office',
            'stopPoint',
            'driverParcel.details.parcelDetail.parcel.customer',
        ])
            ->where('status', 'pending')
            ->orderBy('requestedAt', 'asc')
            ->get();

        // Group by trip
        $tripsWithPending = $trips->map(function ($trip) use ($pendingArrivals) {
            $trip->pendingArrivals = $pendingArrivals->filter(function ($arrival) use ($trip) {
                return $arrival->driverParcel && $arrival->driverParcel->tripId === $trip->tripId;
            });

            return $trip;
        })->filter(function ($trip) {
            return $trip->pendingArrivals->isNotEmpty();
        });

        return view('admin.trip-management.index', [
            'trips' => $tripsWithPending,
            'allPendingArrivals' => $pendingArrivals,
        ]);
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
     * Approve a stop point arrival.
     */
    public function approveArrival(Request $request, int $arrivalId): JsonResponse
    {
        $request->validate([
            'onTime' => 'required|boolean',
            'comment' => 'nullable|string|max:1000',
        ]);

        $arrival = TripStopPointArrival::findOrFail($arrivalId);

        if ($arrival->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'تم الموافقة على هذه النقطة مسبقاً',
            ], 422);
        }

        $user = Auth::user();
        $arrival->approve($user->id, $request->onTime, $request->comment);

        // Create tracking record for customer
        $this->createTrackingForStopPoint($arrival);

        return response()->json([
            'success' => true,
            'message' => 'تم الموافقة على النقطة بنجاح',
        ]);
    }

    /**
     * Reject a stop point arrival.
     */
    public function rejectArrival(Request $request, int $arrivalId): JsonResponse
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $arrival = TripStopPointArrival::findOrFail($arrivalId);

        if ($arrival->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'تم التعامل مع هذه النقطة مسبقاً',
            ], 422);
        }

        $user = Auth::user();
        $arrival->reject($user->id, $request->comment);

        // Create tracking record for customer
        $this->createTrackingForStopPoint($arrival);

        return response()->json([
            'success' => true,
            'message' => 'تم رفض النقطة بنجاح',
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
}
