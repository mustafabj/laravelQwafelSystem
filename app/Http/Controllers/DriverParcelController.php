<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDriverParcelRequest;
use App\Http\Requests\UpdateDriverParcelItemStatusRequest;
use App\Http\Requests\UpdateDriverParcelStatusRequest;
use App\Models\Driver;
use App\Models\DriverParcel;
use App\Models\DriverParcelDetail;
use App\Models\Office;
use App\Models\ParcelDetail;
use App\Models\Trip;
use Carbon\Carbon;
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
                'message' => 'حدث خطأ أثناء البحث: '.$e->getMessage(),
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

        if (! $trip) {
            return response()->json([
                'success' => false,
                'message' => 'الرحلة المحددة غير موجودة',
            ], 422);
        }

        try {
            $officeId = $request->officeId ?? $user->officeId ?? session()->get('officeId');

            if (! $officeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب تحديد المكتب (officeId)',
                ], 422);
            }

            $data = [
                'parcelNumber' => $request->parcelNumber,
                'tripId' => $request->tripId,
                'tripDate' => $request->tripDate,
                'driverName' => $request->driverName,
                'driverNumber' => $request->driverNumber,
                'sendTo' => $request->sendTo,
                'officeId' => $officeId,
                'cost' => $request->cost ?? 0,
                'paid' => $request->paid ?? 0,
                'costRest' => $request->costRest ?? 0,
                'currency' => $request->currency ?? 'IQD',
            ];

            $driverParcel = DriverParcel::createWithDetails($data, $user, $request->parcelDetails);

            // Load the saved driver parcel with all relations for the print step
            $driverParcel->load([
                'trip',
                'office',
                'user',
                'details.parcelDetail.parcel.customer',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء إرسالية السائق بنجاح',
                'driverParcelId' => $driverParcel->parcelId,
                'driverParcel' => [
                    'parcelId' => $driverParcel->parcelId,
                    'parcelNumber' => $driverParcel->parcelNumber,
                    'driverName' => $driverParcel->driverName,
                    'driverNumber' => $driverParcel->driverNumber,
                    'sendTo' => $driverParcel->sendTo,
                    'tripDate' => $driverParcel->tripDate ? (is_string($driverParcel->tripDate) ? $driverParcel->tripDate : Carbon::parse($driverParcel->tripDate)->format('Y-m-d')) : null,
                    'cost' => $driverParcel->cost,
                    'paid' => $driverParcel->paid,
                    'costRest' => $driverParcel->costRest,
                    'currency' => $driverParcel->currency,
                    'parcelDate' => $driverParcel->parcelDate,
                    'trip' => $driverParcel->trip ? [
                        'tripId' => $driverParcel->trip->tripId,
                        'tripName' => $driverParcel->trip->tripName,
                        'destination' => $driverParcel->trip->destination,
                    ] : null,
                    'office' => $driverParcel->office ? [
                        'officeId' => $driverParcel->office->officeId,
                        'officeName' => $driverParcel->office->officeName,
                    ] : null,
                    'details' => $driverParcel->details->map(function ($detail) {
                        return [
                            'detailId' => $detail->detailId,
                            'parcelDetailId' => $detail->parcelDetailId,
                            'quantityTaken' => $detail->quantityTaken,
                            'detailInfo' => $detail->detailInfo,
                            'parcelDetail' => $detail->parcelDetail ? [
                                'parcel' => $detail->parcelDetail->parcel ? [
                                    'parcelNumber' => $detail->parcelDetail->parcel->parcelNumber,
                                    'customer' => $detail->parcelDetail->parcel->customer ? [
                                        'customerName' => ($detail->parcelDetail->parcel->customer->FName ?? '').' '.($detail->parcelDetail->parcel->customer->LName ?? ''),
                                    ] : null,
                                ] : null,
                            ] : null,
                        ];
                    })->toArray(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified driver parcel.
     */
    public function show(int $id): View
    {
        $driverParcel = DriverParcel::findWithRelations($id);

        if (! $driverParcel) {
            abort(404);
        }

        // Load tracking history
        $parcelIds = $driverParcel->details()
            ->with('parcelDetail.parcel')
            ->get()
            ->pluck('parcelDetail.parcel.parcelId')
            ->filter()
            ->unique()
            ->toArray();

        $trackingHistory = \App\Models\ParcelTracking::whereIn('parcelId', $parcelIds)
            ->where('driverParcelId', $id)
            ->with(['driverParcelDetail.parcelDetail.parcel.customer'])
            ->orderBy('trackedAt', 'desc')
            ->get();

        return view('DriverParcels.show', compact('driverParcel', 'trackingHistory'));
    }

    /**
     * Print the specified driver parcel.
     */
    public function print(int $id): View
    {
        $driverParcel = DriverParcel::findWithRelations($id);

        if (! $driverParcel) {
            abort(404);
        }

        // Load user's office relationship if officeId exists
        $user = Auth::user();
        $office = null;
        if ($user && $user->officeId) {
            $office = Office::find($user->officeId);
        }

        return view('DriverParcels.print', compact('driverParcel', 'office'));
    }

    /**
     * Update arrival status of driver parcel items.
     */
    public function updateItemStatus(UpdateDriverParcelItemStatusRequest $request, int $id): JsonResponse
    {
        try {
            $driverParcelDetail = DriverParcelDetail::findByDriverParcel($request->detailId, $id);

            if (! $driverParcelDetail) {
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
                'message' => 'حدث خطأ: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update driver parcel status.
     */
    public function updateStatus(UpdateDriverParcelStatusRequest $request, int $id): JsonResponse
    {
        try {
            $driverParcel = DriverParcel::find($id);

            if (! $driverParcel) {
                return response()->json([
                    'success' => false,
                    'message' => 'الإرسالية غير موجودة',
                ], 404);
            }

            $user = Auth::user();
            $trackedBy = $user ? $user->name : 'system';

            // Build description based on status
            $description = match ($request->status) {
                'in_transit' => 'تم بدء نقل الإرسالية - خرج آخر عنصر من المكتب',
                'arrived' => 'وصلت جميع العناصر إلى الوجهة',
                'delivered' => 'تم تسليم الإرسالية',
                default => 'تم تحديث حالة الإرسالية',
            };

            if ($request->delayReason) {
                $description .= ' - '.$request->delayReason;
            }

            $success = $driverParcel->updateStatus(
                $request->status,
                $description,
                $trackedBy
            );

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل تحديث حالة الإرسالية',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة الإرسالية بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: '.$e->getMessage(),
            ], 422);
        }
    }
}
