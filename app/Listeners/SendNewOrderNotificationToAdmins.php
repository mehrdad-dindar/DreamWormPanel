<?php

namespace App\Listeners;

use Exception;
use Log;
use App\Events\OrderCreated;
use App\Services\N8nTelegram;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use function Laravel\Prompts\warning;

class SendNewOrderNotificationToAdmins
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
     * @throws ConnectionException
     */
    public function handle(OrderCreated $event): void
    {
        $message = "🛒 سفارش جدید\n\n";
        $message .= "*مشتری:*\n";
        $message .= $event->customer->name ."\n";
        $message .= $event->customer->phone ."\n\n";
        if (!empty($event->order->getOrderItems())) {
            $message .= "*موارد سفارش:*\n";
            foreach ($event->order->items as $item) {
                $message .= $item->quantity . ' کیلو ' . $item->product->name . "\n";
                $message .= number_format($item->price) . " تومان \n";
            }
        }
        $status = __('status.' . $event->order->status);
        $message .= "\nسفارش در وضعیت *". $status ."* قرار دارد.\n\n";
        $message .= "*نوع تحویل: *" . ($event->order->deliver_type ? "تحوبل درب کارگاه" : $event->customer->address);
        $message .= "\n\nمبلغ کل:";
        $message .= "\n".number_format($event->order->price). " تومان";

        try {
            $n8n = new N8nTelegram();
            $n8n->sendToN8nWebhook([
                'chat_id' => config('services.telegram-bot-api.chat_id'),
                'text' => $message
            ]);
            info('Reminder sent successfully!');
        } catch (Exception $e) {
            Log::error('Failed to send N8nTelegram message: ' . $e->getMessage());
            warning('Failed to send N8nTelegram message: ' . $e->getMessage());
        }
    }
}
