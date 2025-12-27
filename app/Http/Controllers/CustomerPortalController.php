<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DriverParcel;
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

        $customer = Customer::findOrFail($customerId);
        $phone = preg_replace('/[^0-9]/', '', $customer->phone1);

        // Load driver parcel with necessary relationships
        $driverParcel = DriverParcel::with([
            'trip',
            'office',
            'details.parcelDetail.parcel.customer',
            'details.parcelDetail.parcel.originOffice',
            'details.parcelDetail.parcel.destinationOffice',
        ])->findOrFail($parcelId);

        // Verify this parcel belongs to the logged-in customer
        if (! $driverParcel->belongsToCustomerByPhone($phone)) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الإرسالية');
        }

        // Get tracking data using model method
        $trackingData = $driverParcel->getTrackingDataForCustomer($phone);

        return view('customer-portal.track', [
            'driverParcel' => $driverParcel,
            'customer' => $customer,
            'trackingHistory' => $trackingData['trackingHistory'],
            'effectiveStatus' => $trackingData['effectiveStatus'],
            'allCustomerItemsArrived' => $trackingData['allCustomerItemsArrived'],
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
