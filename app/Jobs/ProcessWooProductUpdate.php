<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessWooProductUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $topic;
    public $payload;


    /**
     * Create a new job instance.
     */
    public function __construct($topic, array $payload)
    {
        $this->topic = $topic;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // ترتیب پردازش: product.created, product.updated, product.deleted
            switch ($this->topic) {
                case 'product.created':
                case 'product.updated':
                    $this->syncProduct($this->payload);
                    break;
                case 'product.deleted':
                    $this->deleteProduct($this->payload);
                    break;
                default:
                    Log::info('Unhandled Woo topic', ['topic' => $this->topic]);
            }
        } catch (\Throwable $e) {
            Log::error('Error processing Woo webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // اجازه بده queued job retry شود طبق تنظیمات
        }
    }

    protected function syncProduct(array $payload)
    {
        // ساختار payload ممکنه nest داشته باشه. در webhook محصول معمولاً اطلاعات product در payload موجود است.
        $productData = $payload['product'] ?? $payload; // تنظیم برای حالت‌های مختلف

        // مشخصات کلیدی: sku یا id
        $sku = $productData['sku'] ?? null;
        $wooId = $productData['id'] ?? null;
        $name = $productData['name'] ?? null;
        $price = $productData['price'] ?? $productData['sale_price'] ?? null;
        $stock = $productData['stock_quantity'] ?? null;
        $link = $productData['permalink'] ?? null;
        $description = $productData['short_description'] ?? null;

        // mapping: سعی کن حداقل یک شناسه یکتا داشته باشی (sku یا woo_id)
        $match = [];
        if ($sku) $match['sku'] = $sku;
        elseif ($wooId) $match['woo_id'] = $wooId;
        else {
            Log::warning('Product without sku or id received', ['payload' => $productData]);
            return;
        }

        $data = [
            'name' => $name,
            'price' => $price,
            'stock' => $stock,
            'description' => $description,
            'woo_id' => $wooId,
            'permalink' => $link ?? null,
        ];

        // upsert/update-or-create
        $product = Product::updateOrCreate($match, $data);

        // تصاویر: اگر لینک تصویر باشه دانلود و ذخیره کن (اختیاری ولی مفید)
//        if (!empty($productData['images']) && is_array($productData['images'])) {
//            $this->syncImages($product, $productData['images']);
//        }

        Log::info('Product synced from Woo', ['product_id' => $product->id, 'woo_id' => $wooId]);
    }

    protected function deleteProduct(array $payload)
    {
        $productData = $payload['product'] ?? $payload;
        $wooId = $productData['id'] ?? null;
        $sku = $productData['sku'] ?? null;

        if ($sku) {
            Product::where('sku', $sku)->delete();
        } elseif ($wooId) {
            Product::where('woo_id', $wooId)->delete();
        } else {
            Log::warning('Delete webhook without identifiers', $payload);
        }

        Log::info('Product deleted via webhook', ['payload' => $payload]);
    }

    protected function syncImages($product, array $images)
    {
        foreach ($images as $img) {
            $src = $img['src'] ?? $img['url'] ?? null;
            if (! $src) continue;

            try {
                $response = Http::get($src);
                if ($response->ok()) {
                    $ext = pathinfo(parse_url($src, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                    $filename = 'products/' . $product->id . '/' . uniqid('', true) . '.' . $ext;
                    Storage::disk('public')->put($filename, $response->body());
                    // سپس در مدل product ذخیره یا attach کن (بسته به طراحی مدلت)
                    // مثال: $product->images()->create(['path' => $filename]);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to fetch product image', ['src' => $src, 'error' => $e->getMessage()]);
            }
        }
    }
}
