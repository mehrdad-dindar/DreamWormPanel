<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make('order')
                    ->columns(3)
                ->schema([
                    Forms\Components\Section::make('موارد سفارش')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->label(__('Items'))
                                ->translateLabel()
                                ->relationship()
                                ->reorderable()
                                ->defaultItems(1)
                                ->hiddenLabel()
                                ->schema([
                                    Forms\Components\Grid::make()
                                        ->schema([
                                            Forms\Components\Select::make('product_id')
                                                ->reactive()
                                                ->live()
                                                ->native(false)
                                                ->helperText(fn ($state) => Product::find($state)->helperText ?? '')
                                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                    if (is_null($state)) {
                                                        return null;
                                                    }
                                                    if (!$get('custom_price')){
                                                        $product = Product::findOrFail($get('product_id'));
                                                        $set('price', $product->price * floatval($get('quantity') ?? 1));
                                                    }
                                                })
                                                ->relationship('product', 'name'),
                                            Forms\Components\TextInput::make('quantity')
                                                ->hidden(fn(Get $get) => is_null($get('product_id')))
                                                ->numeric()
                                                ->default(1)
                                                ->step(0.5)
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                    if (is_null($state)) {
                                                        return null;
                                                    }
                                                    if (!$get('custom_price')){
                                                        $product = Product::findOrFail($get('product_id'));
                                                        $set('price', $product->price * floatval($get('quantity')??1));
                                                    }
                                                })
                                                ->label(__('Quantity')),
                                            Forms\Components\Hidden::make('custom_price')
                                                ->live()
                                                ->reactive()
                                                ->dehydrated()
                                                ->default(0),
                                            Forms\Components\TextInput::make('price')
                                                ->hidden(fn(Get $get) => is_null($get('product_id')))
                                                ->prefixActions([
                                                    Action::make('edit_price')
                                                        ->hidden(fn(Get $get) => $get('custom_price'))
                                                        ->translateLabel()
                                                        ->icon('heroicon-o-pencil-square')
                                                        ->action(function (Set $set) {
                                                            $set('custom_price', 1);
                                                        }),
                                                    Action::make('discard_price')
                                                        ->hidden(fn(Get $get) => !$get('custom_price'))
                                                        ->translateLabel()
                                                        ->icon('heroicon-o-x-mark')
                                                        ->action(function (Set $set,Get $get) {
                                                            $product = Product::findOrFail($get('product_id'));
                                                            $set('custom_price', 0);
                                                            $set('price', $product->price * floatval($get('quantity')??1));
                                                        }),
                                                ])
                                                ->suffix('تومان')
                                                ->reactive()
                                                ->live()
                                                ->readOnly(fn(Get $get) => !$get('custom_price'))
                                                ->mutateStateForValidationUsing(fn ($state) => str_replace(',', '', $state))
                                                ->mutateDehydratedStateUsing(fn ($state) => str_replace(',', '', $state))
                                                ->mask(RawJs::make('$money($input)'))
                                                ->label(__('Unit Price')),
                                        ])->columns(3)
                                ])
                                ->columnSpanFull(),
                        ])
                        ->columnSpan(2)
                        ->columns()->icon('heroicon-o-list-bullet'),
                    Forms\Components\Grid::make('sideBar')
                        ->columnSpan(1)
                        ->schema([
                            Forms\Components\Section::make('مشتری')
                                ->icon('heroicon-o-user')
                                ->schema([
                                    Forms\Components\Select::make('customer_id')
                                        ->translateLabel()
                                        ->label('customer')
                                        ->prefixIcon('heroicon-o-user')
                                        ->options(function () {
                                            return User::role('customer')->pluck('id_name', 'id');
                                        })
                                        ->optionsLimit(10)
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->createOptionForm([
                                            Forms\Components\Grid::make()
                                                ->schema([
                                                    Forms\Components\TextInput::make('name')
                                                        ->prefixIcon('heroicon-o-user')
                                                        ->label(__('Customer Name'))
                                                        ->required(),
                                                    Forms\Components\TextInput::make('phone')
                                                        ->label(__('Customer Phone'))
                                                        ->prefixIcon('heroicon-o-phone')
                                                        ->unique()
                                                        ->required(),
                                                ])
                                                ->columns(1),
                                        ])
                                        ->createOptionUsing(function (array $data) {
                                            $user = User::create($data);
                                            $user->assignRole('customer');
                                            return $user->id;
                                        })
                                        ->createOptionAction(fn ($action) => $action->modalWidth('sm'))
                                        ->required(),
                                    Forms\Components\Checkbox::make('deliver_type')
                                        ->live()
                                        ->default(true)
                                        ->reactive()
                                        ->dehydrated(),
                                    Forms\Components\TextInput::make('address')
                                        ->dehydrated()
                                        ->hidden(fn(Get  $get) => $get('deliver_type')),
                                ]),
                            Forms\Components\Hidden::make('price'),
                            Forms\Components\Section::make('صورت حساب')
                                ->icon('heroicon-o-currency-dollar')
                                ->schema([
                                    Forms\Components\Placeholder::make('invoice')
                                        ->hint('Total Price')
                                        ->live()
                                        ->reactive()
                                        ->content(function (Get $get, Set $set) {
                                            $result = self::getInvoice($get);
                                            $set('price', $result['total']);
                                            return $result['html'];
                                        }),
                                ])
                        ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->description(fn($record) => $record->customer->phone)
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Submitted By'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function ($record){
                        return match ($record->status){
                            'pending' => 'warning',
                            'processing' => 'info',
                            'completed' => 'success',
                            default => 'danger'
                        };
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->suffix(' تومان')
                    ->numeric(locale: 'en')
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    private static function getInvoice(Get $get): array
    {
//        dd($get("items"));
        if (!count($get("items"))) {
            return [
                'total' => 0,
                'html' => "موارد سفارش خالی هست !"];
        }
        $html = "";
        $total = 0;

        foreach ($get("items") as $item) {
            if (is_null($item["product_id"])){
                continue;
            }
            $price = str_replace(',', '', $item['price']);
            $total += intval($price);
            $html .= "<div class='flex justify-between items-center'>";
            $product = Product::findOrFail($item["product_id"]);
            $html .= "<span>".$product->name."(".$item['quantity']." کیلو)</span>";
            $html .= "<span>".number_format($price)."</span>";
            $html .= "</div>";
        }

        if (!$get('deliver_type')){
            $delivery = 70000;
            $total += $delivery;
            $html .= "<div class='flex justify-between items-center pt-1'>";
            $html .= "<strong>حمل و نقل</strong>";
            $html .= "<strong>".number_format($delivery)."</strong>";
            $html .= "</div>";
        }

        $html .= "<div class='flex justify-between items-center pt-2'>";
        $html .= "<strong>جمع کل</strong>";
        $html .= "<strong>".number_format($total)." تومان</strong>";
        $html .= "</div>";

        return [
            'total' => $total,
            'html' => new HtmlString($html)
        ];
    }
}
