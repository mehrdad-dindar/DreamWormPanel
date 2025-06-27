<?php

namespace App\Console\Commands;

use App\Events\FifteenDaysPassed;
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
        $orders = Order::whereDate('created_at', now()->subDays(15))->get();
        foreach ($orders as $order) {
            $customer = $order->customer;
            if (!$customer) continue;
            event(new FifteenDaysPassed($customer));
        }
    }
}
