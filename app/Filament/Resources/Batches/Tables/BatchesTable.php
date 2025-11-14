<?php

namespace App\Filament\Resources\Batches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('egg_date', 'desc')
            ->columns([
                TextColumn::make('batch_number')
                    ->translateLabel()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('egg_date')
                    ->translateLabel()
                    ->jalaliDate(format: 'd F Y')
                    ->sortable(),
                TextColumn::make('actual_boxes')
                    ->translateLabel()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('expected_harvest_date')
                    ->translateLabel()
                    ->jalaliDate(format: 'd F Y')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
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
                ]),
            ]);
    }
}
