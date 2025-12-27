<?php

namespace App\Events;

use App\Models\TripStopPointArrival;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TripStopPointArrivalRequested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $arrival;

    public function __construct(TripStopPointArrival $arrival)
    {
        $this->arrival = $arrival->load([
            'driverParcel.trip',
            'driverParcel.office',
            'stopPoint',
            'driverParcel.details.parcelDetail.parcel.customer',
        ]);
    }

    public function broadcastOn(): Channel
    {
        return new Channel('trip-management');
    }

    public function broadcastAs(): string
    {
        return 'arrival-requested';
    }
}
