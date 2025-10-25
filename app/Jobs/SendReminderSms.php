<?php

namespace App\Jobs;

use App\Models\User;
use App\Traits\Sms;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReminderSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Sms;
    public User $customer;

    /**
     * Create a new job instance.
     */
    public function __construct(User $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $phone = $this->customer->phone;

        $this->sendPattern(
            patternCode: self::FOLLOW_UP,
            phone: $phone,
            patternValues:[
                'name' => $this->customer->name
            ]
        );
    }
}
