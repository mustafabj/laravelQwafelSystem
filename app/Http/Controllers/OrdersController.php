<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Parcel;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        return view('Orders.index');
    }

    public function getFormLoadingState()
    {
        return response()->json([
            'html' => view('Orders.partials.form_loading')->render(),
        ]);
    }

    public function getFormErrorState()
    {
        return response()->json([
            'html' => view('Orders.partials.form_error')->render(),
        ]);
    }

    public function getParcelForm()
    {
        $user = Auth::user();
        $currentOfficeId = $user->officeId ?? null;
        
        // Get next parcel number (start from 300, increment from max)
        $lastParcel = Parcel::orderBy('parcelNumber', 'desc')->first();
        $nextParcelNumber = $lastParcel && $lastParcel->parcelNumber >= 300 && $lastParcel->parcelNumber < 10000
            ? $lastParcel->parcelNumber + 1
            : 300;
        
        // Get all offices except current user's office
        $offices = Office::where('officeId', '!=', $currentOfficeId)
            ->orderBy('officeName')
            ->get(['officeId', 'officeName']);
        
        $currentOffice = $currentOfficeId ? Office::find($currentOfficeId) : null;
        
        return response()->json([
            'html' => view('Orders.steps.parcel', [
                'nextParcelNumber' => $nextParcelNumber,
                'offices' => $offices,
                'currentOffice' => $currentOffice,
            ])->render(),
        ]);
    }

    public function getTicketForm()
    {
        $user = Auth::user();
        
        // Get next ticket number (similar logic)
        $lastTicket = Ticket::orderBy('tecketNumber', 'desc')->first();
        $nextTicketNumber = $lastTicket && $lastTicket->tecketNumber >= 1
            ? $lastTicket->tecketNumber + 1
            : 1;
        
        return response()->json([
            'html' => view('Orders.steps.ticket', [
                'nextTicketNumber' => $nextTicketNumber,
            ])->render(),
        ]);
    }
}
