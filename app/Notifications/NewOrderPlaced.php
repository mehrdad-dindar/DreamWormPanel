<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewOrderPlaced extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('سفارش جدید ثبت شد')
            ->icon(asset('imgs/order.png'))
            ->badge(asset('imgs/order.png'))
            ->body('سفارش جدید برای مشتری :  ' . $this->order->customer->name . " و موارد سفارش شامل : " . implode(', ', $this->order->getOrderItems()))
            ->action('مشاهده سفارش', route('filament.admin.resources.orders.edit', $this->order->id))
            ->data(['order_id' => $this->order->id])
            ->options(['TTL' => 3600]);
    }
}
