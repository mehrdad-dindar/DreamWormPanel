<?php

namespace App\Console\Commands;

use App\Models\Batch;
use App\Services\N8nTelegram;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class SendDailyReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily Telegram reminder for batch tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();
        $batches = Batch::all();

        $wateringBatches = [];
        $feedingBatches = [];
        $fertilizingBatches = [];
        $spawningReminder = false;

        foreach ($batches as $batch) {
            // استخراج تاریخ‌های آب‌پاشی
            $wateringDates = collect($batch->watering_dates)->pluck('date')->toArray();
            if (in_array($tomorrow, $wateringDates)) {
                $wateringBatches[] = $batch->batch_number;
            }

            // استخراج تاریخ‌های خوراک‌دهی
            $feedingDates = collect($batch->feeding_dates)->pluck('date')->toArray();
            if (in_array($tomorrow, $feedingDates)) {
                $feedingBatches[] = $batch->batch_number;
            }

            // استخراج تاریخ‌های کودگیری
            $fertilizingDates = collect($batch->fertilization_dates)->pluck('date')->toArray();
            if (in_array($tomorrow, $fertilizingDates)) {
                $fertilizingBatches[] = $batch->batch_number;
            }
        }

        // بررسی یادآوری تخم‌ریزی
        $latestBatch = Batch::latest('batch_number')->first();
        if ($latestBatch) {
            $nextSpawnDate = Carbon::parse($latestBatch->egg_date)->addDays(5)->toDateString();
            if ($nextSpawnDate === $tomorrow) {
                $spawningReminder = true;
            }
        }

        // آماده‌سازی متن پیام
        $message = "🔔 *یادآوری*\n\n";
        $message .= "امور مربوط به فردا " . verta($tomorrow)->format('l j F Y') . "\n\n";

        if ($spawningReminder) {
            $message .= "🥚🥚 تخم‌گیری:\nفردا زمان تخم‌گیری دسته جدید است\n\n";
            $message .= "-----------------------\n\n";
        }

        if (!empty($wateringBatches)) {
            $message .= "💦 آب‌پاشی:\nدسته‌های " . implode(' و ', $wateringBatches) . "\n\n";
        } else {
            $message .= "💦 آب‌پاشی:\nندارد\n\n";
        }

        if (!empty($feedingBatches)) {
            $message .= "🥕 خوراک‌دهی:\nدسته‌های " . implode(' و ', $feedingBatches) . "\n\n";
        } else {
            $message .= "🥕 خوراک‌دهی:\nندارد\n\n";
        }

        if (!empty($fertilizingBatches)) {
            $message .= "💩 کودگیری:\nدسته‌های " . implode(' و ', $fertilizingBatches) . "\n\n";
        } else {
            $message .= "💩 کودگیری:\nندارد\n\n";
        }
        // ارسال پیام به تلگرام
        try {
            $n8n = new N8nTelegram();
            $n8n->sendToN8nWebhook([
                'chat_id' => config('services.telegram-bot-api.chat_id'),
                'text' => $message
            ]);
            $this->info('Reminder sent successfully!');
        } catch (\Exception $e) {
            \Log::error('Failed to send N8nTelegram message: ' . $e->getMessage());
            $this->error('Failed to send N8nTelegram message: ' . $e->getMessage());
        }
    }
}

