<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $id = $request->input('id');

        $ticket = Ticket::with([
            'customer:customerId,FName,LName,customerPassport',
            'address:addressId,city,area,street,buildingNumber,info',
            'office:officeId,officeName',
            'user:id,name',
        ])->findOrFail($id);

        $currencyMap = [
            'JD' => 'دينار',
            'USD' => 'دولار أمريكي',
            'IQD' => 'دينار عراقي',
            'SYP' => 'ليرة سورية',
            'SAR' => 'ريال سعودي',
        ];

        $ticket->currency_name = $currencyMap[$ticket->currency] ?? $ticket->currency;
        $ticket->paid_text = $ticket->paid === 'paid' ? 'مدفوع' : 'غير مدفوع';
        $ticket->unpaid_amount = $ticket->paid !== 'paid'
            ? ($ticket->cost - $ticket->costRest)
            : 0;

        if ($ticket->travelTime) {
            $time = \Carbon\Carbon::parse($ticket->travelTime);
            $period = $time->format('A') === 'AM' ? 'صباحًا' : 'مساءً';
            $ticket->formatted_time = $time->format('h:i').' '.$period;
        } else {
            $ticket->formatted_time = '';
        }

        return response()->json([
            'html' => view('Tickets.show', compact('ticket'))->render(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function fetchLastTickets()
    {
        $tickets = Ticket::orderBy('ticketId', 'desc')->limit(100)->get();

        return response()->json($tickets);
    }

    public function print($id)
    {
        $ticket = Ticket::with([
            'customer',
            'address',
            'office',
            'user',
        ])->findOrFail($id);

        $currencyMap = [
            'JD' => 'دينار',
            'USD' => 'دولار أمريكي',
            'IQD' => 'دينار عراقي',
            'SYP' => 'ليرة سورية',
            'SAR' => 'ريال سعودي',
        ];

        $ticket->currency_name = $currencyMap[$ticket->currency] ?? $ticket->currency;
        $ticket->paid_text = $ticket->paid === 'paid' ? 'مدفوع' : 'غير مدفوع';
        $ticket->unpaid_amount = $ticket->paid !== 'paid'
            ? ($ticket->cost - $ticket->costRest)
            : 0;

        if ($ticket->travelTime) {
            $time = \Carbon\Carbon::parse($ticket->travelTime);
            $period = $time->format('A') === 'AM' ? 'صباحًا' : 'مساءً';
            $ticket->formatted_time = $time->format('h:i').' '.$period;
        } else {
            $ticket->formatted_time = '';
        }

        return view('Tickets.print', compact('ticket'));
    }
}
