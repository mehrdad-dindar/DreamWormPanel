<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Product;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\UnorderedList;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\HtmlString;
use InvalidArgumentException;
use Throwable;
use ToneGabes\Filament\Icons\Enums\Phosphor;
use function Pest\Laravel\get;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(3)
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
                                                    ->prefixIcon(Phosphor::BarcodeDuotone)
                                                    ->reactive()
                                                    ->live()
                                                    ->native(false)
                                                    ->helperText(fn ($state) => Product::find($state)->helperText ?? '')
                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                        if (is_null($state)) {
                                                            return null;
                                                        }
                                                        if (!$get('custom_price')){
                                                            $product = rescue(fn() => Product::findOrFail($get('product_id')));
                                                            $set('price', $product->price ?? 0 * (float)($get('quantity') ?? 1));
                                                        }
                                                    })
                                                    ->relationship('product', 'name'),
                                                TextInput::make('quantity')
                                                    ->translateLabel()
                                                    ->prefixIcon(Phosphor::ScalesDuotone)
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
                                                            $product = rescue(fn() => Product::findOrFail($get('product_id')));
                                                            $set('price', round(($product->price ?? 0) * (float)($get('quantity') ?? 1)));
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
                                                            ->icon(Phosphor::PencilLineDuotone)
                                                            ->action(function (Set $set) {
                                                                $set('custom_price', 1);
                                                            }),
                                                        Action::make('discard_price')
                                                            ->hidden(fn(Get $get) => !$get('custom_price'))
                                                            ->translateLabel()
                                                            ->icon(Phosphor::XDuotone)
                                                            ->action(function (Set $set,Get $get) {
                                                                $product = rescue(fn() => Product::findOrFail($get('product_id')));
                                                                $set('custom_price', 0);
                                                                $set('price', $product->price ?? 0 * (float)($get('quantity') ?? 1));
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
                            ->icon(Phosphor::ListChecksDuotone),
                        Grid::make(1)
                            ->schema([
                                Section::make('مشتری')
                                    ->icon(Phosphor::UserDuotone)
                                    ->schema([
                                        Select::make('customer_id')
                                            ->label(__('Customer'))
                                            ->prefixIcon(Phosphor::UserDuotone)
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
                                                            ->prefixIcon(Phosphor::UserDuotone)
                                                            ->label(__('Customer Name'))
                                                            ->required(),
                                                        TextInput::make('phone')
                                                            ->label(__('Customer Phone'))
                                                            ->prefixIcon(Phosphor::PhoneDuotone)
                                                            ->live()
                                                            ->reactive()
                                                            ->unique(table: "users",column: "phone", ignoreRecord: true)
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
                                                try {
                                                    $user = User::create($data);
                                                    $user->assignRole('customer');
                                                    return $user->id;
                                                } catch (\Throwable $e) {
                                                    Notification::make()
                                                        ->title($e->getCode())
                                                        ->body($e->getMessage())
                                                        ->icon(Phosphor::WarningDuotone)
                                                        ->send()
                                                        ->danger();
                                                    return 0;
                                                }

                                            })
                                            ->createOptionAction(fn ($action) => $action->modalWidth('sm'))
                                            ->required(),
                                        Fieldset::make('Deliver type')
                                            ->translateLabel()
                                            ->schema([
                                                Toggle::make('deliver_type')
                                                    ->label(__('Workshop door'))
                                                    ->live()
                                                    ->offIcon(Phosphor::TruckFill)
                                                    ->onIcon(Phosphor::StorefrontDuotone)
                                                    ->default(true)
                                                    ->reactive()
                                                    ->dehydrated(),
                                                Textarea::make('address')
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
                                    ->icon(Phosphor::InvoiceDuotone)
                                    ->schema([
                                        Toggle::make('send_sms')
                                            ->onIcon(Phosphor::ChatCircleDotsDuotone)
                                            ->offIcon(Phosphor::ChatCircleSlashDuotone)
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
            $total += (int)$price;
            $html .= "<div class='flex justify-between items-center'>";
            $product = Product::findOrFail($item["product_id"]);
            if ($item['quantity'] < 1) {
                $html .= "<span>" . $product?->name . " (" . $item['quantity'] * 1000 . " گرم)</span>";
            }else {
                $html .= "<span>" . $product?->name . " (" . $item['quantity'] . " کیلوگرم)</span>";
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
}
