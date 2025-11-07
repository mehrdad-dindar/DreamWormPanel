<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\BugReportResource\Pages\ListBugReports;
use App\Filament\Resources\BugReportResource\Pages\CreateBugReport;
use App\Filament\Resources\BugReportResource\Pages\EditBugReport;
use App\Filament\Resources\BugReportResource\Pages;
use App\Filament\Resources\BugReportResource\RelationManagers;
use App\Models\BugReport;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BugReportResource extends Resource
{
    protected static ?string $model = BugReport::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bug-ant';
    protected static string | \UnitEnum | null $navigationGroup = 'پشتیبانی و نگهداری';
    protected static ?string $label = 'گزارش باگ';
    protected static ?string $navigationLabel = 'گزارش‌های باگ';
    protected static ?string $breadcrumb = 'گزارش‌های باگ';
    protected static ?string $pluralModelLabel = 'گزارش‌های باگ';

    protected static ?string $modelLabel = 'گزارش';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('user_id')->default(auth()->id()),
            TextInput::make('title')->required()->label('عنوان'),
            Textarea::make('description')->required()->label('توضیحات'),
            Hidden::make('status')
                ->default("pending"),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->label('عنوان'),
                TextColumn::make('status')->badge()->label('وضعیت'),
                TextColumn::make('user.name')->label('کاربر'),
                TextColumn::make('created_at')->since()->label('تاریخ ثبت'),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
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
