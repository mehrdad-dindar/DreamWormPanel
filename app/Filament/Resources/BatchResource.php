<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BatchResource\Pages;
use App\Filament\Resources\BatchResource\RelationManagers;
use App\Models\Batch;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use phpDocumentor\Reflection\Types\Self_;

class BatchResource extends Resource
{
    protected static ?string $model = Batch::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $activeNavigationIcon = 'heroicon-s-inbox-stack';
    protected static ?string $navigationLabel = 'دسته‌های تولید';
    protected static ?string $breadcrumb = 'دسته‌های تولید';
    protected static ?string $pluralModelLabel = 'دسته‌ها';

    protected static ?string $modelLabel = 'دسته';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make()
                    ->schema([
                        Forms\Components\TextInput::make('batch_number')
                            ->translateLabel()
                            ->prefixIcon('heroicon-o-tag')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('actual_boxes')
                            ->translateLabel()
                            ->numeric(),
                        Forms\Components\DatePicker::make('egg_date')
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
                        Forms\Components\DatePicker::make('expected_harvest_date')
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
                            ->default(Batch::calculateDates(type: 'water', period: 5))
                            ->reorderable(false)
                            ->deletable(false)
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn($state) => verta($state['date'])->format('d F Y'))
                            ->schema([
                                Forms\Components\DatePicker::make('date')
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
                                Forms\Components\DatePicker::make('date')
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
                            ->default(Batch::calculateDates(type: 'fertilize',period: 3, interval: 10))
                            ->schema([
                                Forms\Components\DatePicker::make('date')
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
            ->columns([
                Tables\Columns\TextColumn::make('batch_number')
                    ->translateLabel()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('egg_date')
                    ->translateLabel()
                    ->jalaliDate(format: 'd F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('actual_boxes')
                    ->translateLabel()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expected_harvest_date')
                    ->translateLabel()
                    ->jalaliDate(format: 'd F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
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
            'index' => Pages\ListBatches::route('/'),
            'create' => Pages\CreateBatch::route('/create'),
            'edit' => Pages\EditBatch::route('/{record}/edit'),
        ];
    }
}
