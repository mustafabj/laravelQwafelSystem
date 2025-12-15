<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDriverParcelRequest;
use App\Http\Requests\UpdateDriverParcelItemStatusRequest;
use App\Http\Requests\UpdateDriverParcelStatusRequest;
use App\Models\DriverParcel;
use App\Models\DriverParcelDetail;
use App\Models\ParcelDetail;
use App\Models\Trip;
use App\Models\Driver;
use App\Models\Office;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DriverParcelController extends Controller
{
    /**
     * Display a listing of driver parcels.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        
        $filters = [
            'status' => $request->input('status', 'all'),
            'tripId' => $request->input('tripId'),
        ];

        $driverParcels = DriverParcel::getFilteredList($user, $filters);
        $trips = Trip::getActiveForDropdown();
        $statuses = ['pending', 'in_transit', 'arrived', 'delivered'];

        return view('DriverParcels.index', compact('driverParcels', 'trips', 'statuses'));
    }

    /**
     * Show the form for creating a new driver parcel.
     */
    public function create(): View
    {
        $trips = Trip::getActiveTrips();
        $drivers = Driver::getAll();
        $offices = Office::getAll();
        $nextParcelNumber = DriverParcel::getNextParcelNumber();

        return view('DriverParcels.create', compact(
            'trips',
            'drivers',
            'offices',
            'nextParcelNumber'
        ));
    }

    /**
     * Search available parcel details.
     */
    public function searchParcelDetails(Request $request): JsonResponse
    {
        try {
            $searchTerm = $request->input('search', '');
            $limit = $request->input('limit', 20);

            // Trim and validate search term
            $searchTerm = trim($searchTerm);
            
            if (strlen($searchTerm) < 2) {
                return response()->json([
                    'success' => true,
                    'parcelDetails' => [],
                    'message' => 'يرجى إدخال كلمتين على الأقل للبحث',
                ]);
            }

            $parcelDetails = ParcelDetail::searchAvailable($searchTerm, $limit);

            return response()->json([
                'success' => true,
                'parcelDetails' => $parcelDetails,
                'count' => count($parcelDetails),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء البحث: ' . $e->getMessage(),
                'parcelDetails' => [],
            ], 500);
        }
    }

    /**
     * Store a newly created driver parcel.
     */
    public function store(StoreDriverParcelRequest $request): JsonResponse
    {
        $user = Auth::user();
        $trip = Trip::findById($request->tripId);

        if (!$trip) {
            return response()->json([
                'success' => false,
                'message' => 'الرحلة المحددة غير موجودة',
            ], 422);
        }

        try {
            $data = [
                'parcelNumber' => $request->parcelNumber,
                'tripId' => $request->tripId,
                'tripDate' => $request->tripDate,
                'driverName' => $request->driverName,
                'driverNumber' => $request->driverNumber,
                'sendTo' => $request->sendTo,
                'officeId' => session()->get('officeId'),
                'cost' => $request->cost ?? 0,
                'paid' => $request->paid ?? 0,
                'costRest' => $request->costRest ?? 0,
                'currency' => $request->currency ?? 'IQD',
            ];

            $driverParcel = DriverParcel::createWithDetails($data, $user, $request->parcelDetails);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء إرسالية السائق بنجاح',
                'driverParcelId' => $driverParcel->parcelId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified driver parcel.
     */
    public function show(int $id): View
    {
        $driverParcel = DriverParcel::findWithRelations($id);

        if (!$driverParcel) {
            abort(404);
        }

        return view('DriverParcels.show', compact('driverParcel'));
    }

    /**
     * Update arrival status of driver parcel items.
     */
    public function updateItemStatus(UpdateDriverParcelItemStatusRequest $request, int $id): JsonResponse
    {
        try {
            $driverParcelDetail = DriverParcelDetail::findByDriverParcel($request->detailId, $id);
            
            if (!$driverParcelDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'تفاصيل الإرسالية غير متطابقة',
                ], 422);
            }

            if ($request->isArrived) {
                $driverParcelDetail->markAsArrived();
            } else {
                $driverParcelDetail->markAsNotArrived();
            }

            $driverParcel = $driverParcelDetail->driverParcel->fresh();
            $allArrived = $driverParcel->allItemsArrived();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة العنصر بنجاح',
                'allArrived' => $allArrived,
                'driverParcelStatus' => $driverParcel->status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update driver parcel status.
     */
    public function updateStatus(UpdateDriverParcelStatusRequest $request, int $id): JsonResponse
    {
        try {
            $success = DriverParcel::updateStatusById(
                $id,
                $request->status,
                $request->delayReason
            );

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'الإرسالية غير موجودة',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة الإرسالية بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 422);
        }
    }
}
