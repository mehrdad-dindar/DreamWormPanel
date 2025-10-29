<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BugReportResource\Pages;
use App\Filament\Resources\BugReportResource\RelationManagers;
use App\Models\BugReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BugReportResource extends Resource
{
    protected static ?string $model = BugReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-bug-ant';
    protected static ?string $navigationGroup = 'پشتیبانی و نگهداری';
    protected static ?string $label = 'گزارش باگ';
    protected static ?string $navigationLabel = 'گزارش‌های باگ';
    protected static ?string $breadcrumb = 'گزارش‌های باگ';
    protected static ?string $pluralModelLabel = 'گزارش‌های باگ';

    protected static ?string $modelLabel = 'گزارش';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('user_id')->default(auth()->id()),
            Forms\Components\TextInput::make('title')->required()->label('عنوان'),
            Forms\Components\Textarea::make('description')->required()->label('توضیحات'),
            Forms\Components\Hidden::make('status')
                ->default("pending"),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->label('عنوان'),
                Tables\Columns\TextColumn::make('status')->badge()->label('وضعیت'),
                Tables\Columns\TextColumn::make('user.name')->label('کاربر'),
                Tables\Columns\TextColumn::make('created_at')->since()->label('تاریخ ثبت'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListBugReports::route('/'),
            'create' => Pages\CreateBugReport::route('/create'),
            'edit' => Pages\EditBugReport::route('/{record}/edit'),
        ];
    }
}
