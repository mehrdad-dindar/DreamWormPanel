<?php

namespace App\Console\Commands;

use App\Jobs\SendReminderSms;
use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class FollowUpSmsManually extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dreamworm:followup-sms-manually {--offset=0} {--limit=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ارسال دستی پیامک یادآوری برای مشتری‌های غیرتکراری با کنترل offset و limit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $offset = (int) $this->option('offset');
        $limit = (int) $this->option('limit');

        $this->info("شروع ارسال پیامک از ردیف {$offset} تا " . ($offset + $limit - 1));

        // انتخاب مشتری‌های غیرتکراری (بر اساس آخرین سفارش هر مشتری)
        $users = User::orderBy('id', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        if ($users->isEmpty()) {
            $this->warn('هیچ مشتری‌ای در این محدوده یافت نشد.');
            return;
        }

        foreach ($users as $customer) {
            SendReminderSms::dispatch($customer);
            $this->line("✅ پیامک برای {$customer->name} ({$customer->phone}) در صف ارسال قرار گرفت.");
        }

        $this->info("📨 {$users->count()} پیامک یادآوری با موفقیت صف‌بندی شدند.");
    }
}
