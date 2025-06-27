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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
                Forms\Components\DatePicker::make('date')
                    ->jalali()
                    ->closeOnDateSelection()
                    ->default(now()),
                Forms\Components\TimePicker::make('start_time')
                    ->jalali()
                    ->seconds(false)
                    ->default(now())
                    ->minutesStep(5)
                    ->required(),
                Forms\Components\TimePicker::make('end_time')
                    ->jalali()
                    ->seconds(false)
                    ->minutesStep(5)
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->jalaliDateTime('d F Y - H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->jalaliDateTime('d F Y - H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('work_time')
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
            'index' => Pages\ListWorkSessions::route('/'),
            'create' => Pages\CreateWorkSession::route('/create'),
            'edit' => Pages\EditWorkSession::route('/{record}/edit'),
        ];
    }
}
