<?php

namespace App\Filament\Resources\BugReports;

use App\Filament\Resources\BugReports\Pages\CreateBugReport;
use App\Filament\Resources\BugReports\Pages\EditBugReport;
use App\Filament\Resources\BugReports\Pages\ListBugReports;
use App\Filament\Resources\BugReports\Schemas\BugReportForm;
use App\Filament\Resources\BugReports\Tables\BugReportsTable;
use App\Models\BugReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class BugReportResource extends Resource
{
    protected static ?string $model = BugReport::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::BugDuotone;
    protected static string | BackedEnum | null $activeNavigationIcon = Phosphor::BugFill;
    protected static string|null|\UnitEnum $navigationGroup = 'پشتیبانی و نگهداری';
    protected static ?string $label = 'گزارش باگ';
    protected static ?string $navigationLabel = 'گزارش‌های باگ';
    protected static ?string $breadcrumb = 'گزارش‌های باگ';
    protected static ?string $pluralModelLabel = 'گزارش‌های باگ';

    protected static ?string $modelLabel = 'گزارش';


    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return BugReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BugReportsTable::configure($table);
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
            'index' => ListBugReports::route('/'),
            'create' => CreateBugReport::route('/create'),
            'edit' => EditBugReport::route('/{record}/edit'),
        ];
    }
}
