<?php

namespace App\Http\Controllers;

use App\Models\Parcel;
use App\Models\Ticket;
use App\Models\DriverParcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user   = Auth::user();
        $officeId = $user->office_id ?? null;
        $role   = $user->role->name ?? 'user';
        $filter = $request->input('filter', 'all');

        $parcels = Parcel::getLastParcels();
        $tickets = Ticket::getLastTickets();
        $driverParcels = DriverParcel::getLastDriverParcels();
        return view('dashboard', compact('parcels', 'tickets', 'driverParcels'));
    }

}
