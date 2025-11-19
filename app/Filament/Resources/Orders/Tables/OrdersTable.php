<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('customer.name')
                    ->translateLabel()
                    ->description(fn($record) => $record->customer->phone)
                    ->sortable(),
                TextColumn::make('order_items')
                    ->translateLabel()
                    ->getStateUsing(fn($record) => $record->getOrderItems())
                    ->color('info')
                    ->badge(),
                TextColumn::make('price')
                    ->translateLabel()
                    ->suffix(' تومان')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('deliver_type')
                    ->translateLabel()
                    ->getStateUsing(function ($record) {
                        if ($record->deliver_type) {
                            return 'تحویل درب کارگاه';
                        }
                        return $record->customer->address->address;
                    }),
                TextColumn::make('user.name')
                    ->label(__('Submitted By'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->translateLabel()
                    ->action(
                        Action::make('changeStatus')
                            ->translateLabel()
                            ->color('info')
                            ->icon('heroicon-o-check')
                            ->schema([
                                Select::make('status')
                                    ->translateLabel()
                                    ->native(false)
                                    ->options([
                                        'pending' => __('status.pending'),
                                        'processing' => __('status.processing'),
                                        'completed' => __('status.completed'),
                                        'cancelled' => __('status.cancelled')
                                    ]),
                            ])
                            ->modalWidth('sm')
                            ->action(function (array $data, Order $record) {
                                $record->update($data);
                            })
                    )
                    ->badge()
                    ->icon(fn($state) => "heroicon-o-" . match ($state) {
                            'pending' => 'clock',
                            'processing' => 'arrow-path',
                            'completed' => 'check-circle',
                            default => 'x-circle'
                        })
                    ->formatStateUsing(fn($state) => __('status.' . $state))
                    ->color(fn($state) => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        default => 'danger'
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->translateLabel()
                    ->jalaliDateTime('d F Y - H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->translateLabel()
                    ->jalaliDateTime('d F Y - H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('Call')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-s-phone')
                    ->url(fn($record) => 'tel:+98' . (int)$record->customer->phone)
                    ->translateLabel(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
