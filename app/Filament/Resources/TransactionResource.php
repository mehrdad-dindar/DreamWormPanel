<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Filament\Resources\TransactionResource\Widgets\MonthlyExpenseChart;
use App\Filament\Resources\TransactionResource\Widgets\MonthlyIncomeChart;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $activeNavigationIcon = 'heroicon-s-banknotes';
    protected static ?string $navigationLabel = 'تراکنش ها';
    protected static ?string $breadcrumb = 'تراکنش ها';
    protected static ?string $pluralModelLabel = 'تراکنش ها';

    protected static ?string $modelLabel = 'تراکنش';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
                Forms\Components\TextInput::make('amount')
                    ->translateLabel()
                    ->mask(RawJs::make('$money($input)'))
                    ->suffix('تومان')
                    ->mutateStateForValidationUsing(fn ($state) => str_replace(',', '', $state))
                    ->mutateDehydratedStateUsing(fn ($state) => str_replace(',', '', $state))
                    ->numeric()
                    ->required(),
                Forms\Components\ToggleButtons::make('type')
                    ->inline()
                    ->default(0)
                    ->boolean(__('income'),__('expense')),
                Forms\Components\TextInput::make('category')
                    ->translateLabel()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->translateLabel()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->translateLabel()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->translateLabel()
                    ->formatStateUsing(fn($state) => match ((int)$state) {0 => __('expense'), 1 => __('income')})
                    ->color(fn($record) => $record->type ? 'success' : 'danger')
                    ->badge(),
                Tables\Columns\TextColumn::make('amount')
                    ->translateLabel()
                    ->numeric(locale: 'en')
                    ->suffix(' تومان')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->translateLabel()
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->translateLabel()
                    ->jalaliDateTime('d F Y - H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->translateLabel()
                    ->jalaliDateTime('d F Y - H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
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
