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
                // Load relationships before auto-approving
                $arrival->load(['driverParcel', 'stopPoint']);

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
        // Use the model's method to create tracking
        $arrival->createTrackingForAutoApproval();
    }
}
