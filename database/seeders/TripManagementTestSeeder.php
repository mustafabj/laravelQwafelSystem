<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Driver;
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

class TripManagementTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating comprehensive test data for Trip Management...');
        $this->command->info('');

        // Get or create user first (needed for offices)
        $user = User::first();
        if (! $user) {
            $user = User::create([
                'name' => 'Test Admin',
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
            ]);
            $this->command->info("✓ User: {$user->name}");
        } else {
            $this->command->info("✓ Using existing user: {$user->name}");
        }

        // Create multiple offices
        $this->command->info('');
        $this->command->info('Creating offices...');
        $offices = [];
        $officeNames = ['مكتب بغداد', 'مكتب البصرة', 'مكتب الموصل', 'مكتب أربيل'];
        foreach ($officeNames as $officeName) {
            $office = Office::firstOrCreate(
                ['officeName' => $officeName],
                [
                    'officeAddress' => "عنوان {$officeName}",
                    'officeImage' => 'default-office.jpg',
                    'userId' => $user->id,
                ]
            );
            $offices[] = $office;
            $this->command->info("  ✓ Office: {$office->officeName}");
        }
        $office = $offices[0]; // Use first office as default

        // Create multiple drivers
        $this->command->info('');
        $this->command->info('Creating drivers...');
        $drivers = [];
        $driverNames = [
            ['name' => 'أحمد محمد', 'phone' => '07501234567'],
            ['name' => 'علي حسن', 'phone' => '07701234568'],
            ['name' => 'خالد إبراهيم', 'phone' => '07901234569'],
            ['name' => 'محمود علي', 'phone' => '07501234570'],
        ];

        foreach ($driverNames as $driverData) {
            $driver = Driver::firstOrCreate(
                ['driverName' => $driverData['name']],
                ['driverPhone' => $driverData['phone']]
            );
            $drivers[] = $driver;
            $this->command->info("  ✓ Driver: {$driver->driverName} ({$driver->driverPhone})");
        }

        // Create multiple trips
        $this->command->info('');
        $this->command->info('Creating trips...');
        $trips = [];
        $tripData = [
            [
                'name' => 'رحلة بغداد - البصرة',
                'destination' => 'البصرة',
                'stops' => [
                    ['name' => 'عمان', 'time' => '05:00'],
                    ['name' => 'الحدود', 'time' => '10:00'],
                    ['name' => 'البصرة', 'time' => '15:00'],
                ],
            ],
            [
                'name' => 'رحلة بغداد - الموصل',
                'destination' => 'الموصل',
                'stops' => [
                    ['name' => 'سامراء', 'time' => '06:00'],
                    ['name' => 'تكريت', 'time' => '09:00'],
                    ['name' => 'الموصل', 'time' => '14:00'],
                ],
            ],
            [
                'name' => 'رحلة بغداد - أربيل',
                'destination' => 'أربيل',
                'stops' => [
                    ['name' => 'بعقوبة', 'time' => '07:00'],
                    ['name' => 'كركوك', 'time' => '11:00'],
                    ['name' => 'أربيل', 'time' => '16:00'],
                ],
            ],
        ];

        foreach ($tripData as $index => $tripInfo) {
            $trip = Trip::create([
                'tripName' => $tripInfo['name'],
                'officeId' => $offices[$index % count($offices)]->officeId,
                'destination' => $tripInfo['destination'],
                'finalArrivalTime' => now()->addHours(8),
                'daysOfWeek' => [1, 2, 3, 4, 5],
                'times' => ['08:00', '14:00'],
                'isActive' => true,
                'notes' => "رحلة اختبار - {$tripInfo['destination']}",
                'createdBy' => $user->id,
            ]);
            $trips[] = $trip;
            $this->command->info("  ✓ Trip: {$trip->tripName}");

            // Create stop points for this trip
            foreach ($tripInfo['stops'] as $order => $stopInfo) {
                $arrivalTime = \Carbon\Carbon::createFromTimeString($stopInfo['time']);
                TripStopPoint::create([
                    'tripId' => $trip->tripId,
                    'stopName' => $stopInfo['name'],
                    'arrivalTime' => $arrivalTime,
                    'order' => $order + 1,
                ]);
            }
            $this->command->info('    → Created '.count($tripInfo['stops']).' stop points');
        }

        // Create multiple customers
        $this->command->info('');
        $this->command->info('Creating customers...');
        $customers = [];
        $customerData = [
            ['FName' => 'محمد', 'LName' => 'أحمد', 'phone1' => '07501111111', 'phone2' => '07701111111'],
            ['FName' => 'علي', 'LName' => 'حسن', 'phone1' => '07502222222', 'phone2' => '07702222222'],
            ['FName' => 'خالد', 'LName' => 'إبراهيم', 'phone1' => '07503333333', 'phone2' => '07703333333'],
            ['FName' => 'أحمد', 'LName' => 'محمود', 'phone1' => '07504444444', 'phone2' => '07704444444'],
            ['FName' => 'حسن', 'LName' => 'علي', 'phone1' => '07505555555', 'phone2' => '07705555555'],
        ];

        foreach ($customerData as $customerInfo) {
            $customer = Customer::firstOrCreate(
                ['phone1' => $customerInfo['phone1']],
                [
                    'FName' => $customerInfo['FName'],
                    'LName' => $customerInfo['LName'],
                    'customerPassport' => 'TEST'.rand(1000, 9999),
                    'phone2' => $customerInfo['phone2'],
                    'phone3' => '',
                    'phone4' => '',
                    'addedDate' => now(),
                ]
            );
            $customers[] = $customer;
            $this->command->info("  ✓ Customer: {$customer->FName} {$customer->LName}");
        }

        // Create parcels for customers
        $this->command->info('');
        $this->command->info('Creating parcels...');
        $parcels = [];
        $lastParcelNumber = Parcel::max('parcelNumber') ?? 0;

        foreach ($customers as $index => $customer) {
            $parcel = Parcel::create([
                'parcelNumber' => ++$lastParcelNumber,
                'customerId' => $customer->customerId,
                'custNumber' => 'CUST'.rand(1000, 9999),
                'parcelDate' => now(),
                'recipientName' => "مستلم {$customer->FName}",
                'recipientNumber' => $customer->phone1,
                'sendTo' => ['بغداد', 'البصرة', 'الموصل', 'أربيل'][$index % 4],
                'cost' => rand(3000, 10000),
                'paid' => 0,
                'costRest' => rand(3000, 10000),
                'currency' => 'IQD',
                'userId' => $user->id,
                'officeId' => $offices[$index % count($offices)]->officeId,
                'officeReId' => $offices[$index % count($offices)]->officeId,
                'accept' => 1,
                'paidMethod' => 'cash',
            ]);
            $parcels[] = $parcel;

            // Create parcel detail
            ParcelDetail::create([
                'parcelId' => $parcel->parcelId,
                'detailQun' => rand(1, 10),
                'detailInfo' => "عنصر اختبار للعميل {$customer->FName}",
            ]);

            $this->command->info("  ✓ Parcel #{$parcel->parcelNumber} for {$customer->FName} {$customer->LName}");
        }

        // Create driver parcels for each trip
        $this->command->info('');
        $this->command->info('Creating driver parcels...');
        $lastDriverParcelNumber = DriverParcel::max('parcelNumber') ?? 0;
        $driverParcels = [];
        $tomorrow = now()->addDay()->format('Y-m-d');
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');

        foreach ($trips as $tripIndex => $trip) {
            // Create 2-3 driver parcels per trip with different dates
            $parcelsPerTrip = rand(2, 3);
            $dates = [$yesterday, $today, $tomorrow];

            for ($i = 0; $i < $parcelsPerTrip; $i++) {
                $driver = $drivers[($tripIndex * $parcelsPerTrip + $i) % count($drivers)];
                $parcel = $parcels[($tripIndex * $parcelsPerTrip + $i) % count($parcels)];
                $parcelDetail = ParcelDetail::where('parcelId', $parcel->parcelId)->first();

                if (! $parcelDetail) {
                    continue;
                }

                $statuses = ['pending', 'in_transit', 'arrived'];
                $status = $statuses[array_rand($statuses)];
                $tripDate = $dates[$i % count($dates)];

                $driverParcel = DriverParcel::create([
                    'parcelNumber' => ++$lastDriverParcelNumber,
                    'tripId' => $trip->tripId,
                    'tripDate' => $tripDate,
                    'driverName' => $driver->driverName,
                    'driverNumber' => $driver->driverPhone,
                    'sendTo' => $trip->destination,
                    'officeId' => $trip->officeId,
                    'parcelDate' => now()->format('Y-m-d H:i:s'),
                    'cost' => rand(3000, 10000),
                    'paid' => 0,
                    'costRest' => rand(3000, 10000),
                    'currency' => 'IQD',
                    'userId' => $user->id,
                    'status' => $status,
                ]);
                $driverParcels[] = $driverParcel;

                // Create driver parcel detail
                DriverParcelDetail::create([
                    'parcelId' => $driverParcel->parcelId,
                    'parcelDetailId' => $parcelDetail->detailId,
                    'detailQun' => $parcelDetail->detailQun,
                    'detailInfo' => $parcelDetail->detailInfo,
                    'quantityTaken' => rand(1, $parcelDetail->detailQun),
                    'isArrived' => $status === 'arrived',
                    'leftOfficeAt' => $status !== 'pending' ? now()->subHours(rand(1, 5)) : null,
                ]);

                $this->command->info("  ✓ Driver Parcel #{$driverParcel->parcelNumber} - {$driver->driverName} - {$trip->tripName} - Date: {$tripDate} - Status: {$status}");
            }
        }

        // Create arrival requests for some driver parcels
        $this->command->info('');
        $this->command->info('Creating arrival requests...');
        $arrivalCount = 0;

        foreach ($driverParcels as $driverParcel) {
            if ($driverParcel->status === 'pending') {
                continue; // Skip pending parcels
            }

            $tripStopPoints = TripStopPoint::where('tripId', $driverParcel->tripId)->get();
            $arrivedPoints = rand(0, min(2, $tripStopPoints->count() - 1)); // Arrive at 0-2 points

            foreach ($tripStopPoints->take($arrivedPoints + 1) as $index => $stopPoint) {
                $isPending = ($index === $arrivedPoints); // Last point is pending
                $status = $isPending ? 'pending' : (rand(0, 1) ? 'approved' : 'auto_approved');

                TripStopPointArrival::create([
                    'driverParcelId' => $driverParcel->parcelId,
                    'stopPointId' => $stopPoint->stopPointId,
                    'arrivedAt' => now()->subMinutes(rand(5, 60)),
                    'expectedArrivalTime' => $stopPoint->arrivalTime,
                    'status' => $status,
                    'requestedAt' => $isPending ? now()->subMinutes(rand(1, 10)) : now()->subHours(rand(1, 3)),
                    'approvedAt' => $status !== 'pending' ? now()->subMinutes(rand(1, 30)) : null,
                    'onTime' => rand(0, 1) ? true : false,
                ]);
                $arrivalCount++;
            }
        }

        $this->command->info("  ✓ Created {$arrivalCount} arrival records");
        $this->command->info('');
        $this->command->info('✅ Trip Management test data created successfully!');
        $this->command->info('');
        $this->command->info('Summary:');
        $this->command->info('  - Offices: '.count($offices));
        $this->command->info('  - Drivers: '.count($drivers));
        $this->command->info('  - Trips: '.count($trips));
        $this->command->info('  - Customers: '.count($customers));
        $this->command->info('  - Parcels: '.count($parcels));
        $this->command->info('  - Driver Parcels: '.count($driverParcels));
        $this->command->info("  - Arrival Records: {$arrivalCount}");
        $this->command->info('');
        $this->command->info("📅 Dates used: Yesterday ({$yesterday}), Today ({$today}), Tomorrow ({$tomorrow})");
        $this->command->info('');
        $this->command->info('🌐 You can now test the page at: /trip-management');
    }
}
