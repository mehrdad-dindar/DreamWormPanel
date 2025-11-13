<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\Image\Image;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('avatar_url')
                    ->imageEditorMode(2)
                    ->imageEditor()
                    ->circleCropper()
                    ->collection('avatars')
                    ->image()
                    ->avatar(),
//                    ->avatar()
//                    ->imageEditor()
//                    ->directory('avatars')
//                    ->disk('public')
//                    ->deletable()
//                    ->circleCropper()
//                    ->imageEditorAspectRatios([
//                        '4:3',
//                        '1:1',
//                    ])
//                    ->image(),
                TextInput::make('name')
                    ->translateLabel()
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->translateLabel()
                    ->tel()
                    ->reactive()
                    ->live()
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->translateLabel()
                    ->email()
                    ->live()
                    ->reactive()
                    ->required(fn(Get $get) => is_null($get('phone')))
                    ->maxLength(255),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->translateLabel()
                    ->password()
                    ->required()
                    ->maxLength(255),
                Select::make('roles')
                    ->translateLabel()
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
