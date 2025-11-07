<?php

namespace App\Filament\Resources\BugReportResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\BugReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBugReports extends ListRecords
{
    protected static string $resource = BugReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
