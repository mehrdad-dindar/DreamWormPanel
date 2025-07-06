<?php

namespace App\Traits;

use App\Models\Product;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

trait Woo
{
    public function getProducts(): array
    {
        try {
            $response = Http::withBasicAuth(
                'ck_3f4e0cef01357e7d9aab1fbad3171de236c8cb63',
                'cs_4764b357bf3b494e4912111343baafe24283fa75'
            )->get('https://dreamworm.ir/wp-json/wc/v3/products');
            if ($response->successful()){
                $data = $response->json();

                Notification::make()
                    ->success()
                    ->title('اطلاعات با موفقیت از API دریافت شد')
                    ->body('لیست محصولات به صورت کامل دریافت شد')
                    ->send();

                return array_map(function ($item) {
                     return [
                        'id' => $item['id'],
                        'name' => $item['name'],
                        'price' => $item['sale_price'],
                    ];
                }, $data);
            }else{
                Notification::make()
                    ->danger()
                    ->title('خطا در پردازش API')
                    ->body($response->json('message'))
                    ->send();

                return [
                    'error' => 'خطا در پردازش API',
                    'message' => $response->json('message')
                ];
            }
        }catch (\Exception $e){
            Notification::make()
                ->danger()
                ->title($e->getCode())
                ->body($e->getMessage())
                ->send();

            return [
                'error' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }

    public function updateProducts(): void
    {
        if (!key_exists('error', $this->getProducts())) {
            foreach ($this->getProducts() as $product) {
                Product::updateOrCreate(['id'=>$product['id']],[
                    'name' => $product['name'],
                    'price' => $product['price'],
                    ]);
            }
            Notification::make()
                ->success()
                ->title('بروزرسانی کامل شد')
                ->body('اطلاعات محصولات در پایگاه داده بروزرسانی شد.')
                ->send();
        }
    }
}
