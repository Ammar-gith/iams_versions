<?php

namespace App\Listeners;

use App\Events\AdvertisementSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAdvertisementNotification
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
    public function handle(AdvertisementSubmitted $event): void
    {
        //
    }
}
