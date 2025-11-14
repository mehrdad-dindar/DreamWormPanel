<?php

namespace App\Filament\Resources\WorkSessions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('start_time', 'desc')
            ->columns([
                ImageColumn::make('user.avatar_url')
                    ->label(' ')
                    ->width(40)
                    ->circular()
                    ->defaultImageUrl(fn ($record): string => 'https://ui-avatars.com/api/?name=' . $record->user->name . '&color=FFFFFF&background=09090b')
                    ->disk('avatar'),
                TextColumn::make('user.name')
                    ->translateLabel()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->translateLabel()
                    ->jalaliDateTime('d F Y - H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->translateLabel()
                    ->jalaliDateTime('d F Y - H:i')
                    ->sortable(),
                TextColumn::make('work_time')
                    ->translateLabel()
                    ->state(fn($record) => diffHours($record)),
                TextColumn::make('description')
                    ->icon('heroicon-s-clock')
                    ->words(4)
                    ->tooltip(fn($state)=> $state)
                    ->translateLabel(),
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
