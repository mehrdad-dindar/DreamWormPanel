<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\WorkSessionResource\Pages\ListWorkSessions;
use App\Filament\Resources\WorkSessionResource\Pages\CreateWorkSession;
use App\Filament\Resources\WorkSessionResource\Pages\EditWorkSession;
use App\Filament\Resources\WorkSessionResource\Pages;
use App\Filament\Resources\WorkSessionResource\RelationManagers;
use App\Models\WorkSession;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkSessionResource extends Resource
{
    protected static ?string $model = WorkSession::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';
    protected static string | \BackedEnum | null $activeNavigationIcon = 'heroicon-s-clock';
    protected static ?string $navigationLabel = 'جلسات کاری';
    protected static ?string $breadcrumb = 'جلسات کاری';
    protected static ?string $pluralModelLabel = 'جلسات کاری';

    protected static ?string $modelLabel = 'جلسه';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(auth()->id()),
                DatePicker::make('date')
                    ->translateLabel()
                    ->required()
                    ->jalali()
                    ->closeOnDateSelection()
                    ->default(now()),
                Fieldset::make()
                    ->schema([
                        TimePicker::make('start_time')
                            ->translateLabel()
                            ->seconds(false)
                            ->default(now()->format("H:i"))
                            ->required(),
                        TimePicker::make('end_time')
                            ->translateLabel()
                            ->seconds(false)
                            ->required(),
                    ]),
                Textarea::make('description')
                    ->translateLabel()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('start_time', 'desc')
            ->columns([
                ImageColumn::make('user.avatar_url')
                    ->label(' ')
                    ->width(40)
                    ->circular()
                    ->defaultImageUrl(fn ($record): string => 'https://ui-avatars.com/api/?name=' . $record->user->name . '&color=FFFFFF&background=09090b')
                    ->disk('avatar'),
                TextColumn::make('user.name')
                    ->translateLabel()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->translateLabel()
                    ->jalaliDateTime('d F Y - H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->translateLabel()
                    ->jalaliDateTime('d F Y - H:i')
                    ->sortable(),
                TextColumn::make('work_time')
                    ->translateLabel()
                    ->state(function($record) {
                        $duration = verta($record->end_time)->diffMinutes($record->start_time);
                        $hour = intval($duration / 60);
                        $minutes = $duration % 60;
                        if ($hour) {
                            if ($minutes) {
                                return $hour . ' ساعت و ' . $minutes . ' دقیقه';
                            }
                            return $hour . ' ساعت';
                        } else {
                            return $minutes . ' دقیقه';
                        }
                    }),
                TextColumn::make('description')
                    ->icon('heroicon-s-clock')
//                    ->formatStateUsing(fn($state)=> substr($state, 0, 25) . ' ...')
                    ->words(4)
                    ->tooltip(fn($state)=> $state)
                    ->translateLabel(),
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
            'index' => ListWorkSessions::route('/'),
            'create' => CreateWorkSession::route('/create'),
            'edit' => EditWorkSession::route('/{record}/edit'),
        ];
    }
}
