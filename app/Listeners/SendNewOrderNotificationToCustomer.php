<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\OrderItem;
use App\Traits\Sms;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Ippanel\Client;

class SendNewOrderNotificationToCustomer
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
     * @throws GuzzleException
     */
    public function handle(OrderCreated $event): void
    {
        if ($event->sendSms)
            $this->sendPattern(
                patternCode: self::ORDER_SUBMITTED,
                phone: $event->customer->phone,
                patternValues: [
                    'name' => $event->customer->name,
                    'items' => implode(' و ', $event->order->getOrderItems()),
                    'price' => number_format($event->order->price).' تومان'
                ]
            );
    }
}
