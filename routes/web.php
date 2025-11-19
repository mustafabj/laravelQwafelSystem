<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ParcelController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrdersController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Parcels
    Route::post('/fetch-last-parcels', [ParcelController::class, 'fetchLastParcels'])->name('fetch-last-parcels');
    Route::post('/Parcels/show', [ParcelController::class, 'show'])
    ->name('parcel.show');
    Route::get('/print-parcel/{id}', [ParcelController::class, 'print'])->name('parcel.print');
    // Ticktes
    Route::post('/fetch-last-tickets', [TicketController::class, 'fetchLastTickets'])->name('fetch-last-tickets');
    Route::post('/Tickets/show', [TicketController::class, 'show'])
    ->name('ticket.show');
    // Order
    Route::get('/parcels-tickets', [OrdersController::class, 'index'])->name('Orders');
});

require __DIR__.'/auth.php';
