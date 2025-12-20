<?php

namespace App\Http\Controllers;

use App\Models\Parcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParcelController extends Controller
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

        $parcel = Parcel::with([
            'customer:customerId,FName,LName,customerPassport',
            'originOffice:officeId,officeName,officeImage',
            'destinationOffice:officeId,officeName',
            'details' => function ($query) {
                $query->select('detailId', 'parcelId', 'detailInfo', 'detailQun');
            },
        ])->findOrFail($id);

        $user = Auth::user();

        return response()->json([
            'html' => view('Parcels.show', [
                'parcel' => $parcel,
                'user' => $user,
            ])->render(),
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

    public function fetchLastParcels()
    {
        $parcels = Parcel::orderBy('parcelId', 'desc')->limit(100)->get();

        return response()->json($parcels);
    }

    public function print($id)
    {
        $parcel = Parcel::with([
            'customer',
            'details',
            'originOffice',
            'destinationOffice',
            'user',
        ])->findOrFail($id);

        $user = Auth::user();

        return view('Parcels.print', compact('parcel', 'user'));
    }
}
