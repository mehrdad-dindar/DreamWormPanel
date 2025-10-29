<?php

namespace App\Filament\Resources\BugReportResource\Pages;

use App\Filament\Resources\BugReportResource;
use App\Jobs\SendBugReportToGitHub;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateBugReport extends CreateRecord
{
    protected static string $resource = BugReportResource::class;

    protected function afterCreate(): void
    {
        // وقتی رکورد ساخته شد، جاب اجرا بشه
        dispatch(new SendBugReportToGitHub($this->record));

        // نوتیفیکیشن در پنل
        Notification::make()
            ->body("گزارش باگ ثبت و به GitHub ارسال شد ✅")
            ->success();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
