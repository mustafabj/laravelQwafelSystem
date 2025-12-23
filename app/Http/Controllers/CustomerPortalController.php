<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DriverParcel;
use App\Models\ParcelTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CustomerPortalController extends Controller
{
    /**
     * Show login page.
     */
    public function showLogin()
    {
        return view('customer-portal.login');
    }

    /**
     * Handle phone number login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:7',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->phone);

        // Find customer by phone number
        $customer = Customer::where(function ($query) use ($phone) {
            $query->where('phone1', 'like', "%{$phone}%")
                ->orWhere('phone2', 'like', "%{$phone}%")
                ->orWhere('phone3', 'like', "%{$phone}%")
                ->orWhere('phone4', 'like', "%{$phone}%");
        })->first();

        if (! $customer) {
            return back()->withErrors(['phone' => 'لم يتم العثور على رقم الهاتف في النظام'])->withInput();
        }

        // Store customer ID in session
        Session::put('customer_id', $customer->customerId);
        Session::put('customer_name', $customer->fullName);

        return redirect()->route('customer-portal.dashboard');
    }

    /**
     * Show customer dashboard with parcels.
     */
    public function dashboard()
    {
        $customerId = Session::get('customer_id');

        if (! $customerId) {
            return redirect()->route('customer-portal.login');
        }

        $customer = Customer::findOrFail($customerId);
        $parcels = DriverParcel::getParcelsByCustomerPhone($customer->phone1);

        return view('customer-portal.dashboard', [
            'customer' => $customer,
            'parcels' => $parcels,
        ]);
    }

    /**
     * Show tracking details for a specific parcel.
     */
    public function track(int $parcelId)
    {
        $customerId = Session::get('customer_id');

        if (! $customerId) {
            return redirect()->route('customer-portal.login');
        }

        $driverParcel = DriverParcel::with([
            'trip',
            'office',
            'details.parcelDetail.parcel.customer',
            'details.parcelDetail.parcel.originOffice',
            'details.parcelDetail.parcel.destinationOffice',
            'trackingHistory' => function ($query) {
                $query->orderBy('trackedAt', 'desc');
            },
        ])->findOrFail($parcelId);

        // Verify this parcel belongs to the logged-in customer
        $customer = Customer::findOrFail($customerId);
        $phone = preg_replace('/[^0-9]/', '', $customer->phone1);

        $belongsToCustomer = $driverParcel->details()
            ->whereHas('parcelDetail.parcel.customer', function ($query) use ($phone) {
                $query->where('phone1', 'like', "%{$phone}%")
                    ->orWhere('phone2', 'like', "%{$phone}%")
                    ->orWhere('phone3', 'like', "%{$phone}%")
                    ->orWhere('phone4', 'like', "%{$phone}%");
            })
            ->exists();

        if (! $belongsToCustomer) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الإرسالية');
        }

        // Get customer's items in this driver parcel
        $customerItems = $driverParcel->details()
            ->whereHas('parcelDetail.parcel.customer', function ($query) use ($phone) {
                $query->where('phone1', 'like', "%{$phone}%")
                    ->orWhere('phone2', 'like', "%{$phone}%")
                    ->orWhere('phone3', 'like', "%{$phone}%")
                    ->orWhere('phone4', 'like', "%{$phone}%");
            })
            ->with('parcelDetail.parcel')
            ->get();

        $customerItemIds = $customerItems->pluck('detailId')->toArray();
        $customerParcelIds = $customerItems->pluck('parcelDetail.parcel.parcelId')->filter()->unique()->toArray();

        // Get tracking history for customer's items only
        // Track based on the last item that left the office
        $lastItemLeftOffice = $customerItems->whereNotNull('leftOfficeAt')->sortByDesc('leftOfficeAt')->first();

        $trackingHistory = ParcelTracking::whereIn('parcelId', $customerParcelIds)
            ->where('driverParcelId', $parcelId)
            ->where(function ($query) use ($customerItemIds) {
                // Include tracking for customer's items OR general tracking (without specific item)
                $query->whereIn('driverParcelDetailId', $customerItemIds)
                    ->orWhereNull('driverParcelDetailId');
            })
            ->orderBy('trackedAt', 'desc')
            ->get();

        // Determine current status based on customer's items
        $allCustomerItemsArrived = $customerItems->where('isArrived', false)->isEmpty();
        $anyCustomerItemLeft = $customerItems->whereNotNull('leftOfficeAt')->isNotEmpty();

        // Calculate effective status - simple: arrived or not
        $effectiveStatus = 'pending';
        if ($allCustomerItemsArrived && $customerItems->isNotEmpty()) {
            $effectiveStatus = 'arrived';
        } elseif ($anyCustomerItemLeft) {
            $effectiveStatus = 'in_transit';
        }

        return view('customer-portal.track', [
            'driverParcel' => $driverParcel,
            'trackingHistory' => $trackingHistory,
            'customer' => $customer,
            'effectiveStatus' => $effectiveStatus,
            'allCustomerItemsArrived' => $allCustomerItemsArrived,
        ]);
    }

    /**
     * Logout customer.
     */
    public function logout()
    {
        Session::forget(['customer_id', 'customer_name']);

        return redirect()->route('customer-portal.login');
    }
}
