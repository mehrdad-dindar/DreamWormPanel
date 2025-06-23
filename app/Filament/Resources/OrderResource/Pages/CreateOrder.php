<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $customer = User::find($data['customer_id']);
//        dd($data);
        $customer->address()->updateOrCreate(['address' => $data['address']]);
        unset($data['address']);
        return $data;
    }
}
