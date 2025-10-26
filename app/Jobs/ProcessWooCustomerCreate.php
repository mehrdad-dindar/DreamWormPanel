<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessWooCustomerCreate implements ShouldQueue
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
            // ترتیب پردازش: customer.created, customer.updated, customer.deleted
            switch ($this->topic) {
                case 'customer.created':
                case 'customer.updated':
                    $this->syncCustomer($this->payload);
                    break;
                case 'customer.deleted':
                    $this->deleteCustomer($this->payload);
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

    protected function syncCustomer(array $payload)
    {
        info('payload', ['payload' => $payload]);
        // ساختار payload ممکنه nest داشته باشه. در webhook محصول معمولاً اطلاعات customer در payload موجود است.
        $customerData = $payload['customer'] ?? $payload; // تنظیم برای حالت‌های مختلف
        // مشخصات کلیدی: sku یا id
        $name = trim($customerData['first_name']. " " . $customerData['last_name']) ?? "مشتری";
        $phone = $customerData['billing']['phone'] ?? null;
        $email = $customerData['email'] ?? null;
        $avatar_url = $customerData['avatar_url'] ?? null;


        if (!is_null($phone) && Str::startsWith($phone, "+98")) {
            $phone = Str::of($phone)->replace('+98', '0');
        }

        // mapping: سعی کن حداقل یک شناسه یکتا داشته باشی (sku یا woo_id)
        $match = [];
        if ($phone) $match['phone'] = $phone;
        else {
            Log::warning('Customer without Phone received', ['payload' => $customerData]);
            return;
        }

        $data = [
            'name' => $name,
            'email' => $email,
            'avatar_url' => $avatar_url,
        ];

        // upsert/update-or-create
        $customer = User::updateOrCreate($match, $data);
        $customer->assignRole('customer');
        $customer->save();

        // تصاویر: اگر لینک تصویر باشه دانلود و ذخیره کن (اختیاری ولی مفید)
//        if (!empty($productData['images']) && is_array($productData['images'])) {
//            $this->syncImages($product, $productData['images']);
//        }

        Log::info('Customer synced from Woo', ['customer_id' => $customer->id, 'phone' => $phone]);
    }

    protected function deleteCustomer(array $payload)
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
