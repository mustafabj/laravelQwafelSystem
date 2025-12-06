<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Parcel;
use App\Models\ParcelDetail;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function storeParcel(Request $request)
    {
        $request->validate([
            'parcelNumber' => 'required|integer',
            'customerId' => 'required|integer|exists:customer,customerId',
            'recipientName' => 'required|string|max:255',
            'recipientNumber' => 'required|string|max:255',
            'sendTo' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'paid' => 'required|string|in:paid,unpaid,LaterPaid',
            'paidMethod' => 'required|string|in:cash,bank',
            'costRest' => 'nullable|numeric|min:0',
            'officeReId' => 'required|integer|exists:office,officeId',
            'paidInMainOffice' => 'nullable|boolean',
            'packageDetails' => 'required|array|min:1',
            'packageDetails.*.qun' => 'required|integer|min:1',
            'packageDetails.*.desc' => 'nullable|string',
        ]);

        $user = Auth::user();

        try {
            DB::beginTransaction();

            // Create parcel
            $parcel = Parcel::create([
                'parcelNumber' => $request->parcelNumber,
                'customerId' => $request->customerId,
                'parcelDate' => now()->format('Y-m-d H:i:s'),
                'recipientName' => $request->recipientName,
                'recipientNumber' => $request->recipientNumber,
                'sendTo' => $request->sendTo,
                'cost' => $request->cost,
                'currency' => $request->currency,
                'paid' => $request->paid,
                'paidMethod' => $request->paidMethod,
                'costRest' => $request->costRest ?? 0,
                'custNumber' => $request->recipientNumber ?? '',
                'userId' => $user->id,
                'officeReId' => $request->officeReId,
                'officeId' => $user->officeId ?? 0,
                'accept' => 'pending',
                'editToId' => 0,
                'token' => null,
                'paidInMainOffice' => $request->paidInMainOffice ?? false,
            ]);

            // Create parcel details
            foreach ($request->packageDetails as $detail) {
                ParcelDetail::create([
                    'parcelId' => $parcel->parcelId,
                    'detailQun' => $detail['qun'],
                    'detailInfo' => $detail['desc'] ?? '',
                ]);
            }

            DB::commit();

            // Load parcel with relationships for print
            $parcel->load(['customer', 'details', 'destinationOffice', 'originOffice', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الارسالية بنجاح',
                'parcelId' => $parcel->parcelId,
                'printUrl' => route('wizard.parcel.print', $parcel->parcelId),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'فشل حفظ الارسالية: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function storeTicket(Request $request)
    {
        $request->validate([
            'ticketNumber' => 'required|integer',
            'customerId' => 'required|integer|exists:customer,customerId',
            'destination' => 'required|string|max:255',
            'Seat' => 'nullable|string|max:255',
            'travelDate' => 'nullable|date',
            'travelTime' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'paid' => 'required|string|in:paid,unpaid',
            'costRest' => 'nullable|numeric|min:0',
            'addressId' => 'nullable|integer|exists:address,addressId',
        ]);

        $user = Auth::user();

        try {
            $ticket = Ticket::create([
                'tecketNumber' => $request->ticketNumber,
                'customerId' => $request->customerId,
                'cost' => $request->cost,
                'currency' => $request->currency,
                'paid' => $request->paid,
                'costRest' => $request->costRest ?? 0,
                'destination' => $request->destination,
                'Seat' => $request->Seat ?? '',
                'travelDate' => $request->travelDate ?? '',
                'travelTime' => $request->travelTime ?? '',
                'ticketDate' => now()->format('Y-m-d H:i:s'),
                'custNumber' => $user->phone ?? '',
                'userId' => $user->id,
                'addressId' => $request->addressId ?? 0,
                'accept' => 'pending',
                'officeId' => $user->officeId ?? 0,
                'token' => null,
            ]);

            // Load ticket with relationships for print
            $ticket->load(['customer', 'address', 'office', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ التذكرة بنجاح',
                'ticketId' => $ticket->ticketId,
                'printUrl' => route('wizard.ticket.print', $ticket->ticketId),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل حفظ التذكرة: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function printParcel($id)
    {
        $parcel = Parcel::with(['customer', 'details', 'destinationOffice', 'originOffice', 'user'])->findOrFail($id);
        return view('Orders.print.parcel', compact('parcel'));
    }

    public function printTicket($id)
    {
        $ticket = Ticket::with(['customer', 'address', 'office', 'user'])->findOrFail($id);
        return view('Orders.print.ticket', compact('ticket'));
    }
}
