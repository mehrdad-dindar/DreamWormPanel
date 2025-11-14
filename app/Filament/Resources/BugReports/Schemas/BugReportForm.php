<?php

namespace App\Filament\Resources\BugReports\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BugReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')->default(auth()->id()),
                TextInput::make('title')->required()->label('عنوان'),
                Textarea::make('description')->required()->label('توضیحات'),
                Hidden::make('status')
                    ->default("pending"),

            ]);
    }
}
