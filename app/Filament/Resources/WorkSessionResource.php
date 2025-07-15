<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkSessionResource\Pages;
use App\Filament\Resources\WorkSessionResource\RelationManagers;
use App\Models\WorkSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkSessionResource extends Resource
{
    protected static ?string $model = WorkSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $activeNavigationIcon = 'heroicon-s-clock';
    protected static ?string $navigationLabel = 'جلسات کاری';
    protected static ?string $breadcrumb = 'جلسات کاری';
    protected static ?string $pluralModelLabel = 'جلسات کاری';

    protected static ?string $modelLabel = 'جلسه';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
                Forms\Components\DatePicker::make('date')
                    ->translateLabel()
                    ->required()
                    ->jalali()
                    ->closeOnDateSelection()
                    ->default(now()),
                Forms\Components\Fieldset::make()
                    ->schema([
                        Forms\Components\TimePicker::make('start_time')
                            ->translateLabel()
                            ->seconds(false)
                            ->default(now()->format("H:i"))
                            ->required(),
                        Forms\Components\TimePicker::make('end_time')
                            ->translateLabel()
                            ->seconds(false)
                            ->required(),
                    ]),
                Forms\Components\Textarea::make('description')
                    ->translateLabel()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('start_time', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('user.avatar_url')
                    ->label(' ')
                    ->width(40)
                    ->circular()
                    ->defaultImageUrl(fn ($record): string => 'https://ui-avatars.com/api/?name=' . $record->user->name . '&color=FFFFFF&background=09090b')
                    ->disk('avatar'),
                Tables\Columns\TextColumn::make('user.name')
                    ->translateLabel()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->translateLabel()
                    ->jalaliDateTime('d F Y - H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->translateLabel()
                    ->jalaliDateTime('d F Y - H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('work_time')
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
                Tables\Columns\TextColumn::make('description')
                    ->icon('heroicon-s-clock')
                    ->formatStateUsing(fn($state)=> substr($state, 0, 25) . ' ...')
                    ->tooltip(fn($state)=> $state)
                    ->translateLabel(),
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
            'index' => Pages\ListWorkSessions::route('/'),
            'create' => Pages\CreateWorkSession::route('/create'),
            'edit' => Pages\EditWorkSession::route('/{record}/edit'),
        ];
    }
}
