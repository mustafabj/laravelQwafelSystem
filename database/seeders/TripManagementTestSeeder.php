<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\DriverParcel;
use App\Models\DriverParcelDetail;
use App\Models\Office;
use App\Models\Parcel;
use App\Models\ParcelDetail;
use App\Models\Trip;
use App\Models\TripStopPoint;
use App\Models\TripStopPointArrival;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TripManagementTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating test data for Trip Management...');

        // Get or create office
        $office = Office::first();
        if (! $office) {
            $office = Office::create([
                'officeName' => 'مكتب الاختبار',
                'officeAddress' => 'عنوان الاختبار',
            ]);
            $this->command->info("Created office: {$office->officeName}");
        } else {
            $this->command->info("Using existing office: {$office->officeName}");
        }

        // Get or create user
        $user = User::first();
        if (! $user) {
            $user = User::create([
                'name' => 'Test Admin',
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
            ]);
            $this->command->info("Created user: {$user->name}");
        } else {
            $this->command->info("Using existing user: {$user->name}");
        }

        // Create trip with stop points
        $trip = Trip::create([
            'tripName' => 'رحلة اختبار إدارة الرحلات',
            'officeId' => $office->officeId,
            'destination' => 'بغداد',
            'finalArrivalTime' => now()->addHours(5),
            'daysOfWeek' => [1, 2, 3, 4, 5],
            'times' => ['08:00', '14:00'],
            'isActive' => true,
            'notes' => 'رحلة اختبار للصفحة الإدارية',
            'createdBy' => $user->id,
        ]);
        $this->command->info("Created trip: {$trip->tripName}");

        // Create stop points for the trip
        $stopPoints = [
            ['stopName' => 'نقطة توقف 1 - الكرادة', 'arrivalTime' => now()->addHours(1), 'order' => 1],
            ['stopName' => 'نقطة توقف 2 - المنصور', 'arrivalTime' => now()->addHours(2), 'order' => 2],
            ['stopName' => 'نقطة توقف 3 - الكاظمية', 'arrivalTime' => now()->addHours(3), 'order' => 3],
        ];

        foreach ($stopPoints as $stopData) {
            $stopPoint = TripStopPoint::create([
                'tripId' => $trip->tripId,
                'stopName' => $stopData['stopName'],
                'arrivalTime' => $stopData['arrivalTime'],
                'order' => $stopData['order'],
            ]);
            $this->command->info("Created stop point: {$stopPoint->stopName}");
        }

        // Get or create customer
        $customer = Customer::first();
        if (! $customer) {
            $customer = Customer::create([
                'FName' => 'عميل',
                'LName' => 'اختبار',
                'phone1' => '07501234567',
                'phone2' => '07701234567',
                'addedDate' => now(),
            ]);
            $this->command->info("Created customer: {$customer->FName} {$customer->LName}");
        } else {
            $this->command->info("Using existing customer: {$customer->FName} {$customer->LName}");
        }

        // Get or create parcel
        $parcel = Parcel::where('customerId', $customer->customerId)->first();
        if (! $parcel) {
            $parcel = Parcel::create([
                'parcelNumber' => Parcel::max('parcelNumber') + 1 ?? 1,
                'customerId' => $customer->customerId,
                'parcelDate' => now(),
                'recipientName' => 'مستلم اختبار',
                'recipientNumber' => '07701234567',
                'sendTo' => 'بغداد',
                'cost' => 5000,
                'paid' => 0,
                'costRest' => 5000,
                'currency' => 'IQD',
                'userId' => $user->id,
                'officeId' => $office->officeId,
                'officeReId' => $office->officeId,
            ]);
            $this->command->info("Created parcel: {$parcel->parcelNumber}");
        } else {
            $this->command->info("Using existing parcel: {$parcel->parcelNumber}");
        }

        // Get or create parcel detail
        $parcelDetail = ParcelDetail::where('parcelId', $parcel->parcelId)->first();
        if (! $parcelDetail) {
            $parcelDetail = ParcelDetail::create([
                'parcelId' => $parcel->parcelId,
                'detailQun' => 5,
                'detailInfo' => 'عنصر اختبار للرحلة',
                'quantityAvailable' => 5,
            ]);
            $this->command->info("Created parcel detail");
        } else {
            $this->command->info("Using existing parcel detail");
        }

        // Create or update driver parcel with in_transit status
        $driverParcel = DriverParcel::where('tripId', $trip->tripId)
            ->whereIn('status', ['in_transit', 'arrived'])
            ->first();

        if (! $driverParcel) {
            // Create new driver parcel
            $lastParcelNumber = DriverParcel::max('parcelNumber') ?? 0;
            $driverParcel = DriverParcel::create([
                'parcelNumber' => $lastParcelNumber + 1,
                'tripId' => $trip->tripId,
                'tripDate' => now()->format('Y-m-d'),
                'driverName' => 'سائق اختبار',
                'driverNumber' => '07701234567',
                'sendTo' => 'بغداد',
                'officeId' => $office->officeId,
                'parcelDate' => now()->format('Y-m-d H:i:s'),
                'cost' => 5000,
                'paid' => 0,
                'costRest' => 5000,
                'currency' => 'IQD',
                'userId' => $user->id,
                'status' => 'in_transit',
            ]);
            $this->command->info("Created driver parcel: {$driverParcel->parcelNumber}");

            // Create driver parcel detail
            $driverParcelDetail = DriverParcelDetail::create([
                'parcelId' => $driverParcel->parcelId,
                'parcelDetailId' => $parcelDetail->detailId,
                'detailQun' => 5,
                'detailInfo' => 'عنصر اختبار',
                'quantityTaken' => 3,
                'isArrived' => false,
                'leftOfficeAt' => now()->subMinutes(30), // Left 30 minutes ago
            ]);
            $this->command->info("Created driver parcel detail");
        } else {
            // Update existing driver parcel to in_transit if needed
            if ($driverParcel->status !== 'in_transit' && $driverParcel->status !== 'arrived') {
                $driverParcel->update(['status' => 'in_transit']);
                $this->command->info("Updated driver parcel status to in_transit");
            } else {
                $this->command->info("Using existing driver parcel: {$driverParcel->parcelNumber}");
            }

            // Ensure at least one detail exists
            $driverParcelDetail = DriverParcelDetail::where('parcelId', $driverParcel->parcelId)->first();
            if (! $driverParcelDetail && $parcelDetail) {
                $driverParcelDetail = DriverParcelDetail::create([
                    'parcelId' => $driverParcel->parcelId,
                    'parcelDetailId' => $parcelDetail->detailId,
                    'detailQun' => 5,
                    'detailInfo' => 'عنصر اختبار',
                    'quantityTaken' => 3,
                    'isArrived' => false,
                    'leftOfficeAt' => now()->subMinutes(30),
                ]);
                $this->command->info("Created driver parcel detail");
            }
        }

        // Get all stop points for the trip
        $tripStopPoints = TripStopPoint::where('tripId', $trip->tripId)->get();

        // Create pending arrival requests for each stop point
        $createdCount = 0;
        foreach ($tripStopPoints as $stopPoint) {
            // Check if arrival already exists
            $existingArrival = TripStopPointArrival::where('driverParcelId', $driverParcel->parcelId)
                ->where('stopPointId', $stopPoint->stopPointId)
                ->where('status', 'pending')
                ->first();

            if (! $existingArrival) {
                TripStopPointArrival::create([
                    'driverParcelId' => $driverParcel->parcelId,
                    'stopPointId' => $stopPoint->stopPointId,
                    'arrivedAt' => now()->subMinutes(rand(5, 20)), // Arrived 5-20 minutes ago
                    'expectedArrivalTime' => $stopPoint->arrivalTime,
                    'status' => 'pending',
                    'requestedAt' => now()->subMinutes(rand(1, 10)), // Requested 1-10 minutes ago
                ]);
                $createdCount++;
                $this->command->info("Created pending arrival for: {$stopPoint->stopName}");
            } else {
                $this->command->info("Pending arrival already exists for: {$stopPoint->stopName}");
            }
        }

        $this->command->info("✅ Trip Management test data created successfully!");
        $this->command->info("   - Trip: {$trip->tripName} (ID: {$trip->tripId})");
        $this->command->info("   - Driver Parcel: {$driverParcel->parcelNumber} (ID: {$driverParcel->parcelId})");
        $this->command->info("   - Pending Arrivals: {$createdCount} new arrivals created");
        $this->command->info("");
        $this->command->info("You can now test the page at: /trip-management");
    }
}
