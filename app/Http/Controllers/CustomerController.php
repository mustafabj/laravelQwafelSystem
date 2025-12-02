<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function getCustomers(Request $request)
    {
        $search = trim($request->input('search', ''));
        $state = $request->input('state'); // 'initial', 'loading', 'no-results', or null
    
        // Handle special states
        if ($state === 'loading') {
            return response()->json([
                'html' => view('Orders.partials.customer_loading')->render(),
            ]);
        }
    
        if ($state === 'initial' || (strlen($search) < 2 && !$state)) {
            return response()->json([
                'html' => view('Orders.partials.customer_empty_state', ['type' => 'initial'])->render(),
            ]);
        }
    
        if ($state === 'no-results') {
            return response()->json([
                'html' => view('Orders.partials.customer_empty_state', ['type' => 'no-results'])->render(),
            ]);
        }
    
        $customers = Customer::search($search)
            ->limit(20) 
            ->get();
    
        if ($customers->isEmpty()) {
            return response()->json([
                'html' => view('Orders.partials.customer_empty_state', ['type' => 'no-results'])->render(),
            ]);
        }
    
        return response()->json([
            'html' => view('Orders.partials.customer_rows', compact('customers'))->render(),
        ]);
    }

    public function getCustomer(Request $request)
    {
        $customerId = $request->input('customerId');
        
        $customer = Customer::with('addresses')->findOrFail($customerId);
        
        return response()->json([
            'customerId' => $customer->customerId,
            'FName' => $customer->FName,
            'LName' => $customer->LName,
            'phone1' => $customer->phone1 ?? '',
            'phone2' => $customer->phone2 ?? '',
            'phone3' => $customer->phone3 ?? '',
            'phone4' => $customer->phone4 ?? '',
            'addresses' => $customer->addresses->map(function ($address) {
                return [
                    'addressId' => $address->addressId,
                    'city' => $address->city ?? '',
                    'area' => $address->area ?? '',
                    'street' => $address->street ?? '',
                    'buildingNumber' => $address->buildingNumber ?? '',
                    'info' => $address->info ?? '',
                ];
            }),
        ]);
    }

    public function storeAddress(Request $request)
    {
        $request->validate([
            'customerId' => 'required|exists:customer,customerId',
            'city' => 'required|string|max:255',
            'area' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'buildingNumber' => 'required|string|max:255',
            'info' => 'nullable|string',
        ]);

        $address = \App\Models\Address::create([
            'customerId' => $request->customerId,
            'city' => $request->city,
            'area' => $request->area,
            'street' => $request->street,
            'buildingNumber' => $request->buildingNumber,
            'info' => $request->info ?? '',
            'addedDay' => now(),
        ]);

        return response()->json([
            'success' => true,
            'address' => [
                'addressId' => $address->addressId,
                'city' => $address->city,
                'area' => $address->area,
                'street' => $address->street,
                'buildingNumber' => $address->buildingNumber,
                'info' => $address->info,
            ],
        ]);
    }

    public function getPhoneItem(Request $request)
    {
        $phoneNumber = $request->input('phoneNumber', '');
        $phoneIndex = $request->input('phoneIndex', 1);
        
        return response()->json([
            'html' => view('Orders.partials.phone_item', [
                'phoneNumber' => $phoneNumber,
                'phoneIndex' => $phoneIndex,
            ])->render(),
        ]);
    }

    public function updatePhones(Request $request)
    {
        $customerId = $request->input('customerId');
        $phones = $request->input('phones', []);
        
        $customer = Customer::findOrFail($customerId);
        
        $customer->phone1 = $phones[0] ?? '';
        $customer->phone2 = $phones[1] ?? '';
        $customer->phone3 = $phones[2] ?? '';
        $customer->phone4 = $phones[3] ?? '';
        
        $customer->save();
        
        return response()->json(['success' => true]);
    }

    public function updateAddress(Request $request)
    {
        $request->validate([
            'addressId' => 'required|exists:address,addressId',
            'city' => 'required|string|max:255',
            'area' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'buildingNumber' => 'required|string|max:255',
            'info' => 'nullable|string',
        ]);

        $address = \App\Models\Address::findOrFail($request->addressId);
        
        $address->city = $request->city;
        $address->area = $request->area;
        $address->street = $request->street;
        $address->buildingNumber = $request->buildingNumber;
        $address->info = $request->info ?? '';
        $address->save();

        return response()->json([
            'success' => true,
            'address' => [
                'addressId' => $address->addressId,
                'city' => $address->city,
                'area' => $address->area,
                'street' => $address->street,
                'buildingNumber' => $address->buildingNumber,
                'info' => $address->info,
            ],
        ]);
    }

    public function getAddressRows(Request $request)
    {
        $addresses = $request->input('addresses', []);
        
        return response()->json([
            'html' => view('Orders.partials.address_rows', compact('addresses'))->render(),
        ]);
    }

    public function getAddressEmptyState()
    {
        return response()->json([
            'html' => view('Orders.partials.address_empty_state')->render(),
        ]);
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'FName' => 'required|string|max:255',
            'LName' => 'required|string|max:255',
            'phoneNumber' => 'required|string|max:255',
            'passport' => 'nullable|string|max:255',
            'custState' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'aria' => 'nullable|string|max:255',
            'streetName' => 'nullable|string|max:255',
            'buildingNumber' => 'nullable|string|max:255',
            'descAddress' => 'nullable|string',
        ]);

        // Create customer
        $customer = Customer::create([
            'FName' => $request->FName,
            'LName' => $request->LName,
            'customerPassport' => $request->passport ?? '',
            'customerState' => $request->custState ?? '',
            'phone1' => $request->phoneNumber,
            'phone2' => '',
            'phone3' => '',
            'phone4' => '',
            'addedDate' => now(),
        ]);

        // Create address if provided
        if ($request->city || $request->aria || $request->streetName || $request->buildingNumber) {
            \App\Models\Address::create([
                'customerId' => $customer->customerId,
                'city' => $request->city ?? '',
                'area' => $request->aria ?? '',
                'street' => $request->streetName ?? '',
                'buildingNumber' => $request->buildingNumber ?? '',
                'info' => $request->descAddress ?? '',
                'addedDay' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'customerId' => $customer->customerId,
            'message' => 'تم اضافة العميل بنجاح',
        ]);
    }
    
}
