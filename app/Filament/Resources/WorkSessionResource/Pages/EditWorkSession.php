<?php

namespace App\Filament\Resources\WorkSessionResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\WorkSessionResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkSession extends EditRecord
{
    protected static string $resource = WorkSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['start_time'] = Carbon::parse(implode(' ',[$data['date'],$data['start_time']]));
        $data['end_time'] = Carbon::parse(implode(' ',[$data['date'],$data['end_time']]));
        unset($data['date']);
        return $data;
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['date'] = $data['start_time'];
        return $data;
    }
}
