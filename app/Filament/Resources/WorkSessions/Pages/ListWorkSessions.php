<?php

namespace App\Filament\Resources\WorkSessions\Pages;

use App\Filament\Resources\WorkSessions\Widgets\WorkSessionChart;
use App\Filament\Resources\WorkSessions\WorkSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkSessions extends ListRecords
{
    protected static string $resource = WorkSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WorkSessionChart::class
        ];
    }
}
