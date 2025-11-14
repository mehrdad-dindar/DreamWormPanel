<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                    ->translateLabel()
                    ->required()
                    ->maxLength(255),
                TextInput::make('price')
                    ->translateLabel()
                    ->required()
                    ->numeric()
                    ->suffix('تومان برای هر کیلو'),
                TextInput::make('sku')
                    ->label('SKU'),
                TextInput::make('woo_id')
                    ->numeric(),
                TextInput::make('stock')
                    ->numeric(),
                TextInput::make('permalink'),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
