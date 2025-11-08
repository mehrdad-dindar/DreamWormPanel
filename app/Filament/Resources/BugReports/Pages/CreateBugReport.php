<?php

namespace App\Filament\Resources\BugReports\Pages;

use App\Filament\Resources\BugReports\BugReportResource;
use App\Jobs\SendBugReportToGitHub;
use App\Models\BugReport;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

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
}
