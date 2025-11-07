<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use App\Filament\Resources\TransactionResource\Pages\CreateTransaction;
use App\Filament\Resources\TransactionResource\Pages\EditTransaction;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Filament\Resources\TransactionResource\Widgets\MonthlyExpenseChart;
use App\Filament\Resources\TransactionResource\Widgets\MonthlyIncomeChart;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static string | \BackedEnum | null $activeNavigationIcon = 'heroicon-s-banknotes';
    protected static ?string $navigationLabel = 'تراکنش ها';
    protected static ?string $breadcrumb = 'تراکنش ها';
    protected static ?string $pluralModelLabel = 'تراکنش ها';

    protected static ?string $modelLabel = 'تراکنش';
    public static function form(Schema $schema): Schema
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

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('user.avatar_url')
                    ->label(' ')
                    ->width(40)
                    ->circular()
                    ->defaultImageUrl(fn ($record): string => 'https://ui-avatars.com/api/?name=' . $record->user->name . '&color=FFFFFF&background=09090b')
                    ->disk('avatar'),
                TextColumn::make('user.name')
                    ->translateLabel()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type')
                    ->translateLabel()
                    ->formatStateUsing(fn($state) => match ((int)$state) {0 => __('expense'), 1 => __('income')})
                    ->color(fn($record) => $record->type ? 'success' : 'danger')
                    ->badge(),
                TextColumn::make('amount')
                    ->translateLabel()
                    ->numeric(locale: 'en')
                    ->suffix(' تومان')
                    ->sortable(),
                TextColumn::make('category')
                    ->translateLabel()
                    ->badge()
                    ->searchable(),
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'edit' => EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            MonthlyIncomeChart::class,
            MonthlyExpenseChart::class
        ];
    }
}
