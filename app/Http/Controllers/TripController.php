<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripRequest;
use App\Models\Office;
use App\Models\Trip;
use App\Models\TripStopPoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TripController extends Controller
{
    /**
     * Display a listing of trips.
     */
    public function index(Request $request): View
    {
        $trips = Trip::getAllWithRelations();
        $offices = Office::getAll();

        return view('Trips.index', compact('trips', 'offices'));
    }

    /**
     * Show the form for creating a new trip.
     */
    public function create(): View
    {
        $offices = Office::getAll();

        return view('Trips.create', compact('offices'));
    }

    /**
     * Store a newly created trip.
     */
    public function store(StoreTripRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $data = $request->validated();

            // Extract stop points
            $stopPoints = $data['stopPoints'] ?? [];
            unset($data['stopPoints']);

            $trip = Trip::createTrip($data, $user->id);

            // Create stop points if provided
            if (! empty($stopPoints)) {
                TripStopPoint::createForTrip($trip->tripId, $stopPoints);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الرحلة بنجاح',
                'tripId' => $trip->tripId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: '.$e->getMessage(),
            ], 422);
        }
    }
}
