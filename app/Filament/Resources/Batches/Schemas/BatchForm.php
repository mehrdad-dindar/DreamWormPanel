<?php

namespace App\Filament\Resources\Batches\Schemas;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\View\FormsIconAlias;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use App\Models\Batch;
use Carbon\Carbon;
use Filament\Forms\Components\Repeater;
use Filament\Support\Icons\Heroicon;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class BatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                    Section::make("اطلاعات تخم‌گیری")
                        ->description('با استفاده از این بخش شما فقط باید تاریخ تخم‌گیری و تعداد جعبه‌ها را وارد کنید')
                        ->icon(Phosphor::Tray->duotone())
                        ->columns()
                        ->schema([
                            TextInput::make('batch_number')
                                ->translateLabel()
                                ->prefixIcon('heroicon-o-tag')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->numeric(),
                            TextInput::make('actual_boxes')
                                ->translateLabel()
                                ->numeric(),
                            DatePicker::make('egg_date')
                                ->hintAction(
                                    Action::make('today')
                                        ->icon('heroicon-o-calendar')
                                        ->action(fn(Set $set) => $set('egg_date', Carbon::today()))
                                        ->translateLabel(),
                                )
                                ->prefixIcon('heroicon-o-calendar-days')
                                ->live()
                                ->reactive()
                                ->translateLabel()
                                ->default(today())
                                ->afterStateUpdated(function(Set $set,$state) {
                                    $set('expected_harvest_date', Carbon::parse($state)->addDays(70));
                                    $set('watering_dates', Batch::calculateDates(type: 'water', start_at: $state, period: 14,interval: 1));
                                    $set('feeding_dates', Batch::calculateDates(type: 'feed', start_at: $state, period: 22, interval: 2));
                                    $set('fertilization_dates', Batch::calculateDates(type: 'fertilize', start_at: $state, period: 2, interval: 20));
                                })
                                ->jalali()
                                ->required(),
                            DatePicker::make('expected_harvest_date')
                                ->jalali()
                                ->reactive()
                                ->default(fn(Get $get) => Carbon::parse($get('egg_date'))->addDays(70))
                                ->helperText('۷۰ روز پس از تخم گیری')
                                ->readOnly()
                                ->translateLabel(),
                        ]),
                    Section::make('تاریخ‌های عملیات')
                        ->columns(3)
                        ->description('تاریخ‌های عملیات‌هایی مثل آبپاشی، خوراک‌دهی و کودگیری در این بخش به صورت خودکار محاسبه شده و قابل ویرایش هستند.')
                        ->schema([
                            Repeater::make('watering_dates')
                                ->translateLabel()
                                ->defaultItems(7)
                                ->default(Batch::calculateDates(type: 'water', period: 14,interval: 1))
                                ->reorderable(false)
                                ->deletable(false)
                                ->collapsible()
                                ->collapsed()
                                ->itemLabel(fn($state) => verta($state['date'])->format('d F Y'))
                                ->schema([
                                    DatePicker::make('date')
                                        ->translateLabel()
                                        ->jalali()
                                        ->required(),
                                ]),
                            Repeater::make('feeding_dates')
                                ->reorderable(false)
                                ->deletable(false)
                                ->collapsible()
                                ->collapsed()
                                ->itemLabel(fn($state) => verta($state['date'])->format('d F Y'))
                                ->translateLabel()
                                ->default(Batch::calculateDates(type: 'feed',period: 22,interval: 2))
                                ->schema([
                                    DatePicker::make('date')
                                        ->translateLabel()
                                        ->jalali()
                                        ->required(),
                                ]),
                            Repeater::make('fertilization_dates')
                                ->reorderable(false)
                                ->deletable(false)
                                ->collapsible()
                                ->collapsed()
                                ->itemLabel(fn($state) => verta($state['date'])->format('d F Y'))
                                ->translateLabel()
                                ->default(Batch::calculateDates(type: 'fertilize',period: 2, interval: 20))
                                ->schema([
                                    DatePicker::make('date')
                                        ->translateLabel()
                                        ->jalali()
                                        ->required(),
                                ]),
                        ])
                        ->collapsed(),
                ]
            );
    }
}
