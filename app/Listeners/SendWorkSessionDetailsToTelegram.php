<?php

namespace App\Listeners;

use App\Events\WorkSessionCreated;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class SendWorkSessionDetailsToTelegram
{
    use InteractsWithQueue;
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
    public function handle(WorkSessionCreated $event): void
    {
        $message = "🦺 گزارش‌کار جدید ثبت شد!\n\n";
        $message .= ".:: توضیحات ::.\n";
        $message .= $event->workSession->description. "\n\n";
        $message .= ".:: ثبت کننده ::.\n";
        $message .= $event->user->name . "\n\n";
        $message .= ".:: تاریخ و زمان ::.\n";
        $message .= verta($event->workSession->start_time)->format("d F") . " - " . verta($event->workSession->start_time)->format("H:i") . " الی " . verta($event->workSession->end_time)->format("H:i") . "\n";
        $message .= "(".diffHours($event->workSession) . ")\n";

        try {
            Notification::route('telegram', config('services.telegram-bot-api.chat_id'))
                ->notifyNow(new class($message) extends \Illuminate\Notifications\Notification {
                    protected $message;

                    public function __construct($message)
                    {
                        $this->message = $message;
                    }

                    public function via($notifiable)
                    {
                        return ['telegram'];
                    }

                    public function toTelegram($notifiable)
                    {
                        return TelegramMessage::create()
                            ->button('ثبت گزارش', route('filament.admin.resources.work-sessions.create'))
                            ->button('ثبت دسته', route('filament.admin.resources.batches.create'))
                            ->button('ثبت تراکنش', route('filament.admin.resources.transactions.create'))
                            ->content($this->message);
                    }
                });
            \Filament\Notifications\Notification::make()
                ->title('اطلاع رسانی گزارش‌کار')
                ->body("گزارش‌کار ثبت شده توسط ".$event->user->name." به ربات تلگرام ارسال شد!")
                ->success()
                ->sendToDatabase(
                    User::all(),
                    true
                )
                ->send();
        } catch (Exception $e) {
            \Log::error('Failed to send workSession to Telegram message: ' . $e->getMessage());
        }
    }
}
