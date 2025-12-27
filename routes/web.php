<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\DriverParcelController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ParcelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Customer Portal Routes (Public - phone-based authentication)
Route::prefix('customer-portal')->name('customer-portal.')->group(function () {
    Route::get('/login', [CustomerPortalController::class, 'showLogin'])->name('login');
    Route::post('/login', [CustomerPortalController::class, 'login']);
    Route::get('/dashboard', [CustomerPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/track/{parcelId}', [CustomerPortalController::class, 'track'])->name('track');
    Route::post('/logout', [CustomerPortalController::class, 'logout'])->name('logout');
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
    Route::get('/print-ticket/{id}', [TicketController::class, 'print'])->name('ticket.print');
    // Order
    Route::get('/parcels-tickets', [OrdersController::class, 'index'])->name('orderWizard');
    Route::post('/get-customers', [CustomerController::class, 'getCustomers'])->name('getCustomers');
    Route::post('/get-customer', [CustomerController::class, 'getCustomer'])->name('getCustomer');
    Route::post('/get-phone-item', [CustomerController::class, 'getPhoneItem'])->name('getPhoneItem');
    Route::post('/update-customer-phones', [CustomerController::class, 'updatePhones'])->name('updateCustomerPhones');
    Route::post('/store-address', [CustomerController::class, 'storeAddress'])->name('storeAddress');
    Route::post('/update-address', [CustomerController::class, 'updateAddress'])->name('updateAddress');
    Route::post('/get-address-rows', [CustomerController::class, 'getAddressRows'])->name('getAddressRows');
    Route::post('/get-address-empty-state', [CustomerController::class, 'getAddressEmptyState'])->name('getAddressEmptyState');
    Route::post('/store-customer', [CustomerController::class, 'storeCustomer'])->name('storeCustomer');

    // Form states
    Route::post('/get-form-loading', [OrdersController::class, 'getFormLoadingState'])->name('getFormLoading');
    Route::post('/get-form-error', [OrdersController::class, 'getFormErrorState'])->name('getFormError');
    Route::get('/wizard/parcel/form', [OrdersController::class, 'getParcelForm'])->name('wizard.parcel.form');
    Route::get('/wizard/ticket/form', [OrdersController::class, 'getTicketForm'])->name('wizard.ticket.form');
    Route::post('/store-parcel', [OrdersController::class, 'storeParcel'])->name('storeParcel');
    Route::post('/store-ticket', [OrdersController::class, 'storeTicket'])->name('storeTicket');
    Route::get('/wizard/parcel/{id}/print', [OrdersController::class, 'printParcel'])->name('wizard.parcel.print');
    Route::get('/wizard/ticket/{id}/print', [OrdersController::class, 'printTicket'])->name('wizard.ticket.print');

    // Driver Parcels
    Route::resource('driver-parcels', DriverParcelController::class);
    Route::get('/driver-parcels/{id}/print', [DriverParcelController::class, 'print'])->name('driver-parcels.print');
    Route::post('/driver-parcels/search-parcel-details', [DriverParcelController::class, 'searchParcelDetails'])->name('driver-parcels.search-parcel-details');
    Route::post('/driver-parcels/{id}/update-item-status', [DriverParcelController::class, 'updateItemStatus'])->name('driver-parcels.update-item-status');
    Route::post('/driver-parcels/{id}/update-status', [DriverParcelController::class, 'updateStatus'])->name('driver-parcels.update-status');

    // Trips
    Route::resource('trips', TripController::class);

    // Admin Trip Management
    Route::prefix('trip-management')->name('admin.trip-management.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AdminTripManagementController::class, 'index'])->name('index');
        Route::get('/trips/{tripId}/arrivals', [\App\Http\Controllers\AdminTripManagementController::class, 'getTripArrivals'])->name('trip.arrivals');
        Route::get('/arrivals/{arrivalId}', [\App\Http\Controllers\AdminTripManagementController::class, 'getArrival'])->name('arrival.show');
        Route::put('/arrivals', [\App\Http\Controllers\AdminTripManagementController::class, 'updateArrival'])->name('arrival.update-new');
        Route::put('/arrivals/{arrivalId}', [\App\Http\Controllers\AdminTripManagementController::class, 'updateArrival'])->name('arrival.update')->where('arrivalId', '[0-9]+');
        Route::post('/driver-parcels/{driverParcelId}/stop-points/{stopPointId}/mark-arrived', [\App\Http\Controllers\AdminTripManagementController::class, 'markArrived'])->name('mark-arrived');
    });

    // Drivers
    Route::resource('drivers', DriverController::class);
    Route::post('/drivers/search', [DriverController::class, 'search'])->name('drivers.search');
});

require __DIR__.'/auth.php';
