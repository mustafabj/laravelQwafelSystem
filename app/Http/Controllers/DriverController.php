<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Models\Driver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverController extends Controller
{
    /**
     * Display a listing of drivers.
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search', ''),
        ];

        $drivers = Driver::getAllWithRelations($filters);

        return view('Drivers.index', compact('drivers'));
    }

    /**
     * Show the form for creating a new driver.
     */
    public function create(): View
    {
        return view('Drivers.create');
    }

    /**
     * Store a newly created driver.
     */
    public function store(StoreDriverRequest $request): JsonResponse
    {
        try {
            $driver = Driver::createDriver($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة السائق بنجاح',
                'driverId' => $driver->driverId,
                'driver' => [
                    'driverId' => $driver->driverId,
                    'driverName' => $driver->driverName,
                    'driverPhone' => $driver->driverPhone,
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
     * Display the specified driver.
     */
    public function show(int $id): JsonResponse|View
    {
        $driver = Driver::findById($id);

        if (! $driver) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'السائق غير موجود',
                ], 404);
            }
            abort(404, 'السائق غير موجود');
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'driver' => [
                    'driverId' => $driver->driverId,
                    'driverName' => $driver->driverName,
                    'driverPhone' => $driver->driverPhone,
                ],
            ]);
        }

        $driver->load(['parcels']);

        return view('Drivers.show', compact('driver'));
    }

    /**
     * Show the form for editing the specified driver.
     */
    public function edit(int $id): View
    {
        $driver = Driver::findById($id);

        if (! $driver) {
            abort(404, 'السائق غير موجود');
        }

        return view('Drivers.edit', compact('driver'));
    }

    /**
     * Update the specified driver.
     */
    public function update(UpdateDriverRequest $request, int $id): JsonResponse
    {
        try {
            $driver = Driver::findById($id);

            if (! $driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'السائق غير موجود',
                ], 404);
            }

            $driver->updateDriver($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات السائق بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Search drivers by name or phone.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search', '');
            $state = $request->input('state', '');

            if ($state) {
                // Return empty state HTML
                $html = view('Drivers.partials.search-states', ['state' => $state])->render();

                return response()->json(['html' => $html]);
            }

            if (strlen($search) < 2) {
                $html = view('Drivers.partials.search-states', ['state' => 'initial'])->render();

                return response()->json(['html' => $html]);
            }

            $drivers = Driver::where(function ($query) use ($search) {
                $query->where('driverName', 'like', "%{$search}%")
                    ->orWhere('driverPhone', 'like', "%{$search}%");
            })
                ->limit(20)
                ->get();

            if ($drivers->isEmpty()) {
                $html = view('Drivers.partials.search-states', ['state' => 'no-results'])->render();

                return response()->json(['html' => $html]);
            }

            $html = view('Drivers.partials.search-results', ['drivers' => $drivers])->render();

            return response()->json(['html' => $html]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified driver.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $driver = Driver::findById($id);

            if (! $driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'السائق غير موجود',
                ], 404);
            }

            // Check if driver has parcels
            if ($driver->parcels()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف السائق لأنه لديه إرساليات مرتبطة به',
                ], 422);
            }

            $driver->deleteDriver();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف السائق بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: '.$e->getMessage(),
            ], 422);
        }
    }
}
