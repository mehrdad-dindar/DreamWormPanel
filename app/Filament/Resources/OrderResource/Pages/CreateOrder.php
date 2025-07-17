<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Events\OrderCreated;
use App\Filament\Resources\OrderResource;
use App\Models\User;
use App\Notifications\NewOrderPlaced;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Notification;
use JetBrains\PhpStorm\NoReturn;
use Spatie\Permission\Models\Role;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
    public bool $sms = false;

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
        if ($data['send_sms']) {
            $this->sms = true;
        }
        $data['status'] = "pending";

        unset($data['send_sms']);
        return $data;
    }

    protected function afterCreate(): void
    {
        event(new OrderCreated($this->record, $this->sms));
        $this->sendNotificationToAdmins($this->record);
    }

//    protected function getRedirectUrl(): string
//    {
//        return $this->getResource()::getUrl('index');
//    }
    private function sendNotificationToAdmins(?\Illuminate\Database\Eloquent\Model $record): void
    {
        $superAdmins = Role::findByName('super_admin')->users;
        Notification::send($superAdmins, new NewOrderPlaced(order: $record));
    }
}
