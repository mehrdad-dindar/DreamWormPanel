<?php

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('user.avatar_url')
                    ->label(' ')
                    ->width(40)
                    ->circular()
                    ->collection('avatars')
                    ->defaultImageUrl(fn ($record): string => 'https://ui-avatars.com/api/?name=' . $record->user->name . '&color=FFFFFF&background=09090b')
                    ->disk('avatar'),
                TextColumn::make('user.name')
                    ->translateLabel()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type')
                    ->translateLabel()
                    ->formatStateUsing(fn($state) => match ((int)$state) {0 => __('expense'), 1 => __('income')})
                    ->color(fn($record) => $record->type ? 'success' : 'danger')
                    ->badge(),
                TextColumn::make('amount')
                    ->translateLabel()
                    ->numeric(locale: 'en')
                    ->suffix(' تومان')
                    ->sortable(),
                TextColumn::make('category')
                    ->translateLabel()
                    ->badge()
                    ->searchable(),
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->label(__("Export Report"))
                        ->exports([
                            ExcelExport::make("table")->withFilename(fn($resource) => verta()->format("Y_m_d_H_i_s_")."transactions")
                                ->withColumns([
                                    Column::make("user.name")
                                        ->heading(__("User")),
                                    Column::make("type")
                                        ->heading(__("Type"))
                                        ->formatStateUsing(fn($state) => match ($state) {0 => __('expense'), 1 => __('income')}),
                                    Column::make("amount")
                                        ->heading(__("Amount"))
                                        ->formatStateUsing(fn($state) => number_format($state). " تومان"),
                                    Column::make("category")
                                        ->heading(__("Category")),
                                    Column::make("created_at")
                                        ->heading(__("Date"))
                                        ->formatStateUsing(fn($state) => verta($state)->format("Y/m/d - H:i")),
                                    Column::make("description")
                                        ->heading(__("Description"))
                                ]),
                        ])
                        ->icon(Phosphor::ExportDuotone),
                ]),
            ]);
    }
}
