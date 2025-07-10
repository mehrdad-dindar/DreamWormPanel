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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $activeNavigationIcon = 'heroicon-s-shopping-cart';
    protected static ?string $navigationLabel = 'سفارشات';
    protected static ?string $breadcrumb = 'سفارشات';
    protected static ?string $pluralModelLabel = 'سفارشات';

    protected static ?string $modelLabel = 'سفارش';

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
                                                ->translateLabel()
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
                                                ->translateLabel()
                                                ->hidden(fn(Get $get) => is_null($get('product_id')))
                                                ->numeric()
                                                ->default(1)
                                                ->step(0.05)
                                                ->reactive()
                                                ->suffix('کیلوگرم')
                                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                    if (is_null($state)) {
                                                        return null;
                                                    }
                                                    if (!$get('custom_price')){
                                                        $product = Product::findOrFail($get('product_id'));
                                                        $set('price', round($product->price * floatval($get('quantity')??1)));
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
                                        ->label(__('Customer'))
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
                                                        ->live()
                                                        ->reactive()
                                                        ->unique(ignoreRecord: true)
                                                        ->rules([
                                                            'regex:/^0[1-9][0-9]\d{8}$/',
                                                        ])
                                                        ->helperText('فرمت مجاز: 0912xxxxxxx یا 021xxxxxxx')
                                                        ->tel()
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
                                    Forms\Components\Fieldset::make('Deliver type')
                                        ->translateLabel()
                                        ->schema([
                                            Forms\Components\Toggle::make('deliver_type')
                                                ->label(__('Workshop door'))
                                                ->live()
                                                ->default(true)
                                                ->reactive()
                                                ->dehydrated(),
                                            Forms\Components\TextInput::make('address')
                                                ->required(fn(Get $get) => !$get('deliver_type'))
                                                ->live()
                                                ->translateLabel()
                                                ->columnSpanFull()
                                                ->dehydrated()
                                                ->hidden(fn(Get  $get) => $get('deliver_type')),
                                        ])
                                ]),
                            Forms\Components\Hidden::make('price'),
                            Forms\Components\Section::make('صورت حساب')
                                ->icon('heroicon-o-currency-dollar')
                                ->schema([
                                    Forms\Components\Toggle::make('send_sms')
                                        ->label(__('Send Invoice SMS')),
                                    Forms\Components\Placeholder::make('invoice')
                                        ->translateLabel()
                                        ->hint(__('Total Price'))
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
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->translateLabel()
                    ->description(fn($record) => $record->customer->phone)
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_items')
                    ->translateLabel()
                    ->getStateUsing(fn($record) => $record->getOrderItems())
                    ->color('info')
                    ->badge(),
                Tables\Columns\TextColumn::make('price')
                    ->translateLabel()
                    ->suffix(' تومان')
                    ->numeric(locale: 'en')
                    ->sortable(),
                Tables\Columns\TextColumn::make('deliver_type')
                    ->translateLabel()
                    ->getStateUsing(function ($record){
                        if ($record->deliver_type) {
                            return 'تحویل درب کارگاه';
                        }
                        return $record->customer->address->address;
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Submitted By'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->translateLabel()
                    ->action(
                        Tables\Actions\Action::make('changeStatus')
                            ->translateLabel()
                            ->color('info')
                            ->icon('heroicon-o-check')
                            ->form([
                                Forms\Components\Select::make('status')
                                    ->translateLabel()
                                    ->native(false)
                                    ->options([
                                        'pending' => __('status.pending'),
                                        'processing' => __('status.processing'),
                                        'completed' => __('status.completed'),
                                        'cancelled' => __('status.cancelled')
                                    ]),
                            ])
                            ->modalWidth('sm')
                            ->action(function (array $data,Order $record) {
                                $record->update($data);
                            })
                    )
                    ->badge()
                    ->icon(fn($state) => "heroicon-o-". match ($state) {
                        'pending' => 'clock',
                        'processing' => 'arrow-path',
                        'completed' => 'check-circle',
                        default => 'x-circle'
                    })
                    ->formatStateUsing(fn($state) => __('status.' . $state))
                    ->color(fn($state) => match ($state){
                            'pending' => 'warning',
                            'processing' => 'info',
                            'completed' => 'success',
                            default => 'danger'
                        })
                    ->sortable(),
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
                Tables\Actions\Action::make('Call')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-s-phone')
                    ->url(fn($record) => 'tel:+98'.intval($record->customer->phone))
                    ->translateLabel(),
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
        if (!count($get("items"))) {
            return [
                'total' => 0,
                'html' => "موارد سفارش خالی هست !"];
        }
        $html = "";
        $total = 0;

        foreach ($get("items") as $item) {
            if (is_null($item["product_id"]) || empty($item["quantity"])){
                continue;
            }
            $price = str_replace(',', '', $item['price']);
            $total += intval($price);
            $html .= "<div class='flex justify-between items-center'>";
            $product = Product::findOrFail($item["product_id"]);
            if ($item['quantity'] < 1) {
                $html .= "<span>" . $product->name . " (" . $item['quantity'] * 1000 . " گرم)</span>";
            }else {
                $html .= "<span>" . $product->name . " (" . $item['quantity'] . " کیلوگرم)</span>";
            }
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

    public static function getNavigationBadge(): ?string
    {
        $pending = Order::whereStatus('pending')->pluck('id');
        if ($pending->count())
            return $pending->count();
        return null;
    }
}
