<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\BatchResource\Pages\ListBatches;
use App\Filament\Resources\BatchResource\Pages\CreateBatch;
use App\Filament\Resources\BatchResource\Pages\EditBatch;
use App\Filament\Resources\BatchResource\Pages;
use App\Filament\Resources\BatchResource\RelationManagers;
use App\Models\Batch;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use phpDocumentor\Reflection\Types\Self_;

class BatchResource extends Resource
{
    protected static ?string $model = Batch::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-inbox-stack';
    protected static string | \BackedEnum | null $activeNavigationIcon = 'heroicon-s-inbox-stack';
    protected static ?string $navigationLabel = 'دسته‌های تولید';
    protected static ?string $breadcrumb = 'دسته‌های تولید';
    protected static ?string $pluralModelLabel = 'دسته‌ها';

    protected static ?string $modelLabel = 'دسته';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make()
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
                                $set('watering_dates', Batch::calculateDates(type: 'water', start_at: $state, period: 5));
                                $set('feeding_dates', Batch::calculateDates(type: 'feed', start_at: $state, period: 14));
                                $set('fertilization_dates', Batch::calculateDates(type: 'fertilize', start_at: $state, period: 3, interval: 10));
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
                            ->default(Batch::calculateDates(type: 'water', period: 7))
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
                            ->default(Batch::calculateDates(type: 'feed',period: 14))
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
                            ->default(Batch::calculateDates(type: 'fertilize',period: 3, interval: 20))
                            ->schema([
                                DatePicker::make('date')
                                    ->translateLabel()
                                    ->jalali()
                                    ->required(),
                            ]),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('egg_date', 'desc')
            ->columns([
                TextColumn::make('batch_number')
                    ->translateLabel()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('egg_date')
                    ->translateLabel()
                    ->jalaliDate(format: 'd F Y')
                    ->sortable(),
                TextColumn::make('actual_boxes')
                    ->translateLabel()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('expected_harvest_date')
                    ->translateLabel()
                    ->jalaliDate(format: 'd F Y')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
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
            'index' => ListBatches::route('/'),
            'create' => CreateBatch::route('/create'),
            'edit' => EditBatch::route('/{record}/edit'),
        ];
    }
}
