<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Filament\Widgets\MonthlyExpenseChart;
use App\Filament\Widgets\MonthlyIncomeChart;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderWidgets(): array
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
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
