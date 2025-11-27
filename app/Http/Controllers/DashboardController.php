<?php

namespace App\Http\Controllers;

use App\Models\DriverParcel;
use App\Models\Parcel;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $totalParcels = Parcel::whereBetween('parcelDate', [$startOfMonth, $endOfMonth])->count();
        $totalTickets = Ticket::whereBetween('ticketDate', [$startOfMonth, $endOfMonth])->count();

        $user = Auth::user();
        $officeId = $user->office_id ?? null;
        $role = $user->role->name ?? 'user';
        $filter = $request->input('filter', 'all');

        $parcels = Parcel::getLastParcels();
        $tickets = Ticket::getLastTickets();
        $driverParcels = DriverParcel::getLastDriverParcels();

        // Calculate accepted and pending counts (both parcels and tickets)
        // Use single queries with sum for better performance
        $acceptedCount = (int) Parcel::where('accept', '!=', 'no')->count() +
                        (int) Ticket::where('accept', '!=', 'no')->count();
        $pendingCount = (int) Parcel::where('accept', 'no')->count() +
                       (int) Ticket::where('accept', 'no')->count();

        return view('dashboard', compact('parcels', 'tickets', 'driverParcels', 'totalParcels', 'totalTickets', 'acceptedCount', 'pendingCount'));
    }
}
