<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Resources\Transactions\Widgets\MonthlyExpenseChart;
use App\Filament\Resources\Transactions\Widgets\MonthlyIncomeChart;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    /*protected function getHeaderWidgets(): array
    {
        return [
            MonthlyExpenseChart::class,
            MonthlyIncomeChart::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make(__('All')),
            1 => Tab::make()
                ->label(__('income'))
                ->query(fn ($query) => $query->whereType(true)),
            0 => Tab::make()
                ->label(__('expense'))
                ->query(fn ($query) => $query->whereType(false)),
        ];
    }*/


    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            MonthlyIncomeChart::class,
            MonthlyExpenseChart::class
        ];
    }
}
