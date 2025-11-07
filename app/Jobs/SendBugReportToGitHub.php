<?php

namespace App\Jobs;

use Exception;
use Log;
use App\Models\BugReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class SendBugReportToGitHub implements ShouldQueue
{
    use Queueable;
    public BugReport $report;

    /**
     * Create a new job instance.
     */
    public function __construct(BugReport $bug)
    {
        $this->report = $bug;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $githubToken = config('services.github.token');
        try {
            $response = Http::withToken($githubToken)->post("https://api.github.com/repos/mehrdad-dindar/DreamWormPanel/issues", [
                'title' => "[Bug] {$this->report->title}",
                'body' => $this->report->description . "\n\nReported by: " . ($this->report->user?->name ?? 'Guest'),
                'labels' => ['bug', 'from-laravel'],
            ]);

            if ($response->successful()) {
                $this->report->update(['status' => 'sent_to_github']);
            }
            $msg = "🐞 باگ جدید گزارش شد:\n{$this->report->title}\n\n{$this->report->description}\n\nReported by: " . ($this->report->user?->name ?? 'Guest');
            $this->sendToTelegram($msg);
        } catch (Exception $e) {
            Log::error("Exeption ". $e->getMessage(), ['exception' => $e]);
        }
    }

    private function sendToTelegram($message): void
    {
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
                            ->button('ثبت گزارش باگ جدید',route('filament.admin.resources.bug-reports.create'))
                            ->content($this->message);
                    }
                });
            Log::info('Bug Report Sent to Telegram');
        } catch (Exception $e) {
            Log::error('Failed to send Telegram message: ' . $e->getMessage());
        }
    }
}
