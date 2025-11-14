<?php

namespace App\Filament\Resources\WorkSessions;

use App\Filament\Resources\WorkSessions\Pages\CreateWorkSession;
use App\Filament\Resources\WorkSessions\Pages\EditWorkSession;
use App\Filament\Resources\WorkSessions\Pages\ListWorkSessions;
use App\Filament\Resources\WorkSessions\Schemas\WorkSessionForm;
use App\Filament\Resources\WorkSessions\Tables\WorkSessionsTable;
use App\Models\WorkSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class WorkSessionResource extends Resource
{
    protected static ?string $model = WorkSession::class;

    protected static ?string $recordTitleAttribute = 'user_id';
    protected static string|BackedEnum|null $navigationIcon = Phosphor::ClockAfternoonThin;
    protected static string | \BackedEnum | null $activeNavigationIcon = Phosphor::ClockAfternoonDuotone;
    protected static ?string $navigationLabel = 'جلسات کاری';
    protected static ?string $breadcrumb = 'جلسات کاری';
    protected static ?string $pluralModelLabel = 'جلسات کاری';

    protected static ?string $modelLabel = 'جلسه';
    public static function form(Schema $schema): Schema
    {
        return WorkSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkSessionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkSessions::route('/'),
            'create' => CreateWorkSession::route('/create'),
            'edit' => EditWorkSession::route('/{record}/edit'),
        ];
    }
}
