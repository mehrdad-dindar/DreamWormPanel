<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(auth()->id()),
                TextInput::make('amount')
                    ->translateLabel()
                    ->mask(RawJs::make('$money($input)'))
                    ->suffix('تومان')
                    ->mutateStateForValidationUsing(fn ($state) => str_replace(',', '', $state))
                    ->mutateDehydratedStateUsing(fn ($state) => str_replace(',', '', $state))
                    ->numeric()
                    ->required(),
                ToggleButtons::make('type')
                    ->inline()
                    ->default(0)
                    ->boolean(__('income'),__('expense')),
                TextInput::make('category')
                    ->translateLabel()
                    ->maxLength(255),
                Textarea::make('description')
                    ->translateLabel()
                    ->columnSpanFull(),
            ]);
    }
}
