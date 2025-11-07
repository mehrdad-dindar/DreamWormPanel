<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Actions\Action;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-cart';
    protected static string | \BackedEnum | null $activeNavigationIcon = 'heroicon-s-shopping-cart';
    protected static ?string $navigationLabel = 'سفارشات';
    protected static ?string $breadcrumb = 'سفارشات';
    protected static ?string $pluralModelLabel = 'سفارشات';

    protected static ?string $modelLabel = 'سفارش';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make('order')
                    ->columns(3)
                ->schema([
                    Section::make('موارد سفارش')
                        ->schema([
                            Repeater::make('items')
                                ->label(__('Items'))
                                ->translateLabel()
                                ->relationship()
                                ->reorderable()
                                ->defaultItems(1)
                                ->hiddenLabel()
                                ->schema([
                                    Grid::make()
                                        ->schema([
                                            Select::make('product_id')
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
                                            TextInput::make('quantity')
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
                                            Hidden::make('custom_price')
                                                ->live()
                                                ->reactive()
                                                ->dehydrated()
                                                ->default(0),
                                            TextInput::make('price')
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
                    Grid::make('sideBar')
                        ->columnSpan(1)
                        ->schema([
                            Section::make('مشتری')
                                ->icon('heroicon-o-user')
                                ->schema([
                                    Select::make('customer_id')
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
                                            Grid::make()
                                                ->schema([
                                                    TextInput::make('name')
                                                        ->prefixIcon('heroicon-o-user')
                                                        ->label(__('Customer Name'))
                                                        ->required(),
                                                    TextInput::make('phone')
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
                                    Fieldset::make('Deliver type')
                                        ->translateLabel()
                                        ->schema([
                                            Toggle::make('deliver_type')
                                                ->label(__('Workshop door'))
                                                ->live()
                                                ->default(true)
                                                ->reactive()
                                                ->dehydrated(),
                                            TextInput::make('address')
                                                ->required(fn(Get $get) => !$get('deliver_type'))
                                                ->live()
                                                ->translateLabel()
                                                ->columnSpanFull()
                                                ->dehydrated()
                                                ->hidden(fn(Get  $get) => $get('deliver_type')),
                                        ])
                                ]),
                            Hidden::make('price'),
                            Section::make('صورت حساب')
                                ->icon('heroicon-o-currency-dollar')
                                ->schema([
                                    Toggle::make('send_sms')
                                        ->label(__('Send Invoice SMS')),
                                    Placeholder::make('invoice')
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
                TextColumn::make('customer.name')
                    ->translateLabel()
                    ->description(fn($record) => $record->customer->phone)
                    ->sortable(),
                TextColumn::make('order_items')
                    ->translateLabel()
                    ->getStateUsing(fn($record) => $record->getOrderItems())
                    ->color('info')
                    ->badge(),
                TextColumn::make('price')
                    ->translateLabel()
                    ->suffix(' تومان')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('deliver_type')
                    ->translateLabel()
                    ->getStateUsing(function ($record){
                        if ($record->deliver_type) {
                            return 'تحویل درب کارگاه';
                        }
                        return $record->customer->address->address;
                    }),
                TextColumn::make('user.name')
                    ->label(__('Submitted By'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->translateLabel()
                    ->action(
                        Action::make('changeStatus')
                            ->translateLabel()
                            ->color('info')
                            ->icon('heroicon-o-check')
                            ->schema([
                                Select::make('status')
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
                Action::make('Call')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-s-phone')
                    ->url(fn($record) => 'tel:+98'.intval($record->customer->phone))
                    ->translateLabel(),
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
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
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
