<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use App\Filament\Resources\ProductResource;
use App\Traits\Woo;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;

class ListProducts extends ListRecords
{
    use Woo;
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('sync')
                ->label(__('Sync by WebSite'))
                ->requiresConfirmation()
                ->icon('heroicon-s-arrows-up-down')
                ->action(fn() => $this->updateProducts())
                ->color(Color::Fuchsia),
        ];
    }
}
