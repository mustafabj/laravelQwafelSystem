<?php

namespace App\Http\Controllers;

use App\Models\Parcel;
use App\Models\Ticket;
use App\Models\DriverParcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $totalParcels = Parcel::whereBetween('parcelDate', [$startOfMonth, $endOfMonth])->count();
        $totalTickets = Ticket::whereBetween('ticketDate', [$startOfMonth, $endOfMonth])->count();
    
        $user   = Auth::user();
        $officeId = $user->office_id ?? null;
        $role   = $user->role->name ?? 'user';
        $filter = $request->input('filter', 'all');

        $parcels = Parcel::getLastParcels();
        $tickets = Ticket::getLastTickets();
        $driverParcels = DriverParcel::getLastDriverParcels();
        return view('dashboard', compact('parcels', 'tickets', 'driverParcels','totalParcels','totalTickets'));
    }

}
