<?php

namespace App\Console\Commands;

use App\Models\TripStopPointArrival;
use Illuminate\Console\Command;

class AutoApproveStopPointArrivals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arrivals:auto-approve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-approve stop point arrivals after 15 minutes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $pendingArrivals = TripStopPointArrival::where('status', 'pending')
            ->whereNotNull('requestedAt')
            ->get();

        $approvedCount = 0;

        foreach ($pendingArrivals as $arrival) {
            if ($arrival->shouldAutoApprove()) {
                if ($arrival->autoApprove()) {
                    $approvedCount++;

                    // Create tracking record
                    $this->createTrackingForArrival($arrival);

                    $this->info("Auto-approved arrival ID: {$arrival->arrivalId}");
                }
            }
        }

        $this->info("Auto-approved {$approvedCount} arrival(s)");

        return Command::SUCCESS;
    }

    /**
     * Create tracking record for auto-approved arrival.
     */
    protected function createTrackingForArrival(TripStopPointArrival $arrival): void
    {
        $driverParcel = $arrival->driverParcel;
        $stopPoint = $arrival->stopPoint;

        if (! $driverParcel || ! $stopPoint) {
            return;
        }

        $parcelIds = $driverParcel->details()
            ->with('parcelDetail.parcel')
            ->get()
            ->pluck('parcelDetail.parcel.parcelId')
            ->filter()
            ->unique()
            ->toArray();

        $description = "وصل السائق إلى نقطة: {$stopPoint->stopName} (موافقة تلقائية)";

        if ($arrival->onTime === false) {
            $description .= ' (تأخر)';
        } elseif ($arrival->onTime === true) {
            $description .= ' (في الوقت المحدد)';
        }

        foreach ($parcelIds as $parcelId) {
            \App\Models\ParcelTracking::createTracking(
                $parcelId,
                $driverParcel->parcelId,
                $driverParcel->tripId,
                'in_transit',
                $stopPoint->stopName,
                $description,
                'system',
                null
            );
        }
    }
}
