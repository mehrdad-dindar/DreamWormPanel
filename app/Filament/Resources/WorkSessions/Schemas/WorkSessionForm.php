<?php

namespace App\Filament\Resources\WorkSessions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class WorkSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(auth()->id()),
                DatePicker::make('date')
                    ->translateLabel()
                    ->required()
                    ->jalali()
                    ->closeOnDateSelection()
                    ->default(now()),
                Fieldset::make()
                    ->schema([
                        TimePicker::make('start_time')
                            ->translateLabel()
                            ->seconds(false)
                            ->default(now()->format("H:i"))
                            ->required(),
                        TimePicker::make('end_time')
                            ->translateLabel()
                            ->seconds(false)
                            ->required(),
                    ]),
                Textarea::make('description')
                    ->translateLabel()
                    ->columnSpanFull(),
            ]);
    }
}
