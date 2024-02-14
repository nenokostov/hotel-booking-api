<?php

namespace App\Listeners;

use App\Models\Staff;
use App\Events\BookingMade;
use App\Notifications\BookingMadeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyStaffOnBookingMade implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BookingMade $event)
    {
        $staffMembers = Staff::all();

        foreach ($staffMembers as $staffMember) {
            $staffMember->notify(new BookingMadeNotification($event->booking));
        }
    }
}