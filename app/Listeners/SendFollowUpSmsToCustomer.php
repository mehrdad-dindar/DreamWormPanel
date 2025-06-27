<?php

namespace App\Listeners;

use App\Events\FifteenDaysPassed;
use App\Traits\Sms;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendFollowUpSmsToCustomer
{
    use Sms;
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
    public function handle(FifteenDaysPassed $event): void
    {
        $this->sendPattern(
            patternCode: self::FOLLOW_UP,
            phone: $event->customer->phone,
            patternValues:[
                'name' => $event->customer->name
            ]
        );
    }
}
