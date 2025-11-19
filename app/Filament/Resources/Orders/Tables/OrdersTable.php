<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use ToneGabes\Filament\Icons\Enums\Phosphor;

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
                SelectFilter::make('user')
                    ->translateLabel()
                    ->preload()
                    ->optionsLimit(10)
                    ->searchable()
                    ->native(false)
                    ->relationship('user', 'name'),
                SelectFilter::make('customer')
                    ->translateLabel()
                    ->preload()
                    ->optionsLimit(10)
                    ->searchable()
                    ->native(false)
                    ->relationship('customer', 'name'),
                SelectFilter::make('status')
                    ->translateLabel()
                    ->preload()
                    ->optionsLimit(10)
                    ->searchable()
                    ->multiple()
                    ->native(false)
                    ->options([
                        'pending' => __('status.pending'),
                        'processing' => __('status.processing'),
                        'completed' => __('status.completed'),
                        'cancelled' => __('status.cancelled')
                    ]),
                Filter::make('created_at')
                    ->schema([
                        Fieldset::make('created_at')
                            ->label(__('Date'))
                            ->schema([
                                DatePicker::make('created_from')
                                    ->label(__('from date'))
                                    ->jalali(),
                                DatePicker::make('created_until')
                                    ->label(__('until date'))
                                    ->jalali(),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
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
                    ExportBulkAction::make()
                        ->label(__("Export Report"))
                        ->exports([
                            ExcelExport::make("table")->withFilename(fn($resource) => verta()->format("Y_m_d_H_i_s_") . "Orders")
                                ->askForWriterType()
                                ->withColumns([
                                    Column::make("user.name")
                                        ->heading(__("Submitted By")),
                                    Column::make("customer.name")
                                        ->heading(__("Customer Name")),
                                    Column::make("order_items")
                                        ->heading(__("Order items"))
                                        ->getStateUsing(fn($record) => $record->getOrderItems()),
                                    Column::make("price")
                                        ->heading(__("Amount"))
                                        ->formatStateUsing(fn($state) => number_format($state) . " تومان"),
                                    Column::make("status")
                                        ->formatStateUsing(fn($state) => match ($state) {
                                            'pending' => __('status.pending'),
                                            'processing' => __('status.processing'),
                                            'completed' => __('status.completed'),
                                            'cancelled' => __('status.cancelled')
                                        })
                                        ->heading(__("Status")),
                                    Column::make("created_at")
                                        ->heading(__("Date"))
                                        ->formatStateUsing(fn($state) => verta($state)->format("Y/m/d - H:i")),
                                ]),
                        ])
                        ->icon(Phosphor::ExportDuotone),
                ]),
            ]);
    }
}
