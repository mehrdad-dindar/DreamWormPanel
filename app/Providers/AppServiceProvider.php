<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Hekmatinasser\Verta\Verta;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        FilamentView::registerRenderHook(
            'panels::global-search.before',
            fn () => Blade::render('@livewire("role_badge")'),
        );
        FilamentView::registerRenderHook(
            'panels::global-search.before',
            fn (): string => Verta::now()->format('l, d M Y'),
        );
    }
}
