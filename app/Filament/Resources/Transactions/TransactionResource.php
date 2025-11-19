<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Schemas\TransactionForm;
use App\Filament\Resources\Transactions\Tables\TransactionsTable;
use App\Filament\Resources\Transactions\Widgets\MonthlyExpenseChart;
use App\Filament\Resources\Transactions\Widgets\MonthlyIncomeChart;
use App\Models\Transaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::CashRegisterThin;
    protected static string | \BackedEnum | null $activeNavigationIcon = Phosphor::CashRegisterDuotone;
    protected static ?string $navigationLabel = 'تراکنش ها';
    protected static ?string $breadcrumb = 'تراکنش ها';
    protected static ?string $pluralModelLabel = 'تراکنش ها';

    protected static ?string $modelLabel = 'تراکنش';

    protected static ?string $recordTitleAttribute = 'user_id';

    public static function form(Schema $schema): Schema
    {
        return TransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionsTable::configure($table);
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
}
