<?php

namespace App\Listeners;

use App\Models\Staff;
use App\Events\BookingCanceled;
use App\Notifications\BookingCanceledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyStaffOnBookingCanceled implements ShouldQueue
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
    public function handle(BookingCanceled $event)
    {
        $staffMembers = Staff::all();

        foreach ($staffMembers as $staffMember) {
            $staffMember->notify(new BookingCanceledNotification($event->booking));
        }
    }
}