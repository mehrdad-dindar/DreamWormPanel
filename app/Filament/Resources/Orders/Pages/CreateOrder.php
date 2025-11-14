<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Events\OrderCreated;
use App\Filament\Resources\Orders\OrderResource;
use App\Notifications\NewOrderPlaced;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    private function sendNotificationToAdmins(?Model $record): void
    {
        $panel_users = Role::findByName('panel_user')->users;
        Notification::send($panel_users, new NewOrderPlaced(order: $record));
    }
}
