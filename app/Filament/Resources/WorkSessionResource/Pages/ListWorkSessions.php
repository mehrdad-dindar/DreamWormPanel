<?php

namespace App\Filament\Resources\WorkSessionResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\WorkSessionResource;
use App\Filament\Resources\WorkSessionResource\Widgets\WorkSessionChart;
use Filament\Actions;
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
