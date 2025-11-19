<?php

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                ]),
            ]);
    }
}
