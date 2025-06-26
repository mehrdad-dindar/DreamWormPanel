<?php

namespace App\Filament\Resources\WorkSessionResource\Pages;

use App\Filament\Resources\WorkSessionResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkSession extends CreateRecord
{
    protected static string $resource = WorkSessionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['start_time'] = Carbon::parse(implode(' ',[$data['date'],$data['start_time']]));
        $data['end_time'] = Carbon::parse(implode(' ',[$data['date'],$data['end_time']]));
        unset($data['date']);
        return $data;
    }
}
