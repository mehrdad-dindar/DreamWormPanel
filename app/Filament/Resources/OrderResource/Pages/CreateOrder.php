<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Events\OrderCreated;
use App\Filament\Resources\OrderResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use JetBrains\PhpStorm\NoReturn;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        if (isset($data['address'])) {
            $customer = User::find($data['customer_id']);
            $customer->address()->updateOrCreate(
                ['user_id' => $customer->id],
                ['address' => $data['address']]
            );
            unset($data['address']);
        }
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    #[NoReturn]
    protected function afterCreate(): void
    {
        event(new OrderCreated($this->record));
    }
}
