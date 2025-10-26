<?php

namespace App\Console\Commands;

use App\Events\FifteenDaysPassed;
use App\Jobs\SendReminderSms;
use App\Models\Order;
use Illuminate\Console\Command;

class SendFollowUpMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dreamworm:send-follow-up-sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::with('customer')
            ->whereDate('created_at', now()->subDays(15))
            ->get();

        foreach ($orders as $order) {
            if ($order->customer) {
                SendReminderSms::dispatch($order->customer);
            }
        }

        info('Fifteen-day reminders dispatched successfully.', [
            $orders->map(function ($order) {
                return [
                    "user_id" => $order->user_id,
                    "phone" => $order->customer->phone,
                    "order_id" => $order->id,
                    "order_price" => $order->price,
                    "created_at" => $order->created_at,
                ];
            })
        ]);
    }
}
