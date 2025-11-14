<?php

namespace App\Filament\Resources\Users\Tables;

use App\Policies\UserPolicy;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
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
                    ->defaultImageUrl(fn ($record): string => 'https://ui-avatars.com/api/?name=' . $record->name . '&color=FFFFFF&background=09090b')
                    ->disk('avatar'),
                TextColumn::make('name')
                    ->translateLabel()
                    ->searchable(),
                TextColumn::make('phone')
                    ->translateLabel()
                    ->searchable(),
                TextColumn::make('email')
                    ->translateLabel()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->translateLabel()
                    ->formatStateUsing(fn($state) => __('role.' . $state))
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'super_admin' => Color::Fuchsia,
                        'customer' => Color::Lime,
                        default => Color::Amber
                    })
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->visible(fn() => UserPolicy::class)
                    ->authorize('call')
                    ->button()
                    ->color(Color::Fuchsia)
                    ->icon('heroicon-s-phone')
                    ->url(fn($record) => 'tel:+98' . (int)$record->phone)
                    ->translateLabel(),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
