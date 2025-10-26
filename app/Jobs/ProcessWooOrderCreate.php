<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessWooOrderCreate implements ShouldQueue
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
            // ترتیب پردازش: order.created, order.updated, order.deleted
            switch ($this->topic) {
                case 'order.created':
                case 'order.updated':
                    $this->syncOrder();
                    break;
                case 'order.deleted':
                    $this->deleteOrder();
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

    protected function syncOrder(): void
    {
        $orderData = $this->payload['order'] ?? $this->payload;

        $customer = $this->getCustomer($orderData);
        $wooId = $orderData['id'] ?? null;
        $price = $orderData['total'] ?? 0;
        $deliverType = $this->getDeliverType($orderData['shipping_lines']);
        $status = $orderData['status'] ?? 'pending';



        // mapping: سعی کن حداقل یک شناسه یکتا داشته باشی (sku یا woo_id)
        $match = [];
        if ($wooId) {
            $match['woo_id'] = $wooId;
        }

        $data = [
            'customer_id' => $customer->id,
            'user_id' => $customer->id,
            'price' => $price,
            'deliver_type' => $deliverType,
            'status' => $status
        ];

        $order = Order::updateOrCreate($match, $data);
        $this->syncOrderItems($order, $orderData['line_items'] ?? []);

        if ($status === "processing"){
            $this->setTransaction($customer);
        }

        Log::info('order synced from Woo', ['order_id' => $order->id, 'woo_id' => $wooId]);
    }

    protected function deleteOrder()
    {
        //
    }

    private function getCustomer(array $orderData)
    {
        $match = [];
        if (isset($orderData['billing']['phone'])) {
            $phone = $orderData['billing']['phone'];
            if (Str::startsWith($phone, "+98")) {
                $phone = Str::of($phone)->replace('+98', '0');
            }
            $match['phone'] = $phone;
        } elseif (isset($orderData['customer_id'])) {
            $match["woo_id"] = $orderData['customer_id'];
        }

        $data = [
            'name' => trim($orderData['billing']['first_name']. " " . $orderData['billing']['last_name']) ?? "مشتری",
            'email' => $orderData['billing']['email'] ?? null,
        ];
        return User::updateOrCreate($match, $data);
    }

    private function getDeliverType(array $shipping_lines): bool
    {
        $shippingId = $shipping_lines[0]['instance_id'] ?? null;
        return $shippingId === "2" || $shippingId === "6";
    }

    protected function syncOrderItems(Order $order, array $lineItems): void
    {
        if (empty($lineItems)) {
            Log::warning('Order has no line items', ['order_id' => $order->id]);
            return;
        }

        foreach ($lineItems as $item) {
            $wooProductId = $item['product_id'] ?? null;
            $quantity = $item['quantity'] ?? 1;
            $price = (int)$item['total'] + (int)$item['total_tax'] ?? 0;

            $product = Product::where('woo_id', $wooProductId)->first();

            $match = [
                'order_id' => $order->id,
                'product_id' => $product?->id,
            ];

            $data = [
                'quantity' => $quantity,
                'price' => $price,
                'custom_price' => false,
            ];

            OrderItem::updateOrCreate($match, $data);
        }

        Log::info('Order items synced', ['order_id' => $order->id]);
    }

    protected function setTransaction(User $customer)
    {
        $description = "پرداخت سفارش شماره: " . $this->payload["id"] . "\n";

        if (isset($this->payload["payment_method_title"])){
            $description .= $this->payload["payment_method_title"] . "\n";
        }

        if (isset($this->payload["transaction_id"])){
            $description .= $this->payload["transaction_id"] . "\n";
        }

        if (isset($this->payload["customer_note"])){
            $description .= "================ توضیحات سفارش ===============\n";
            $description .= $this->payload["customer_note"] . "\n";
        }

        $customer->transactions()->create([
            "type" => true,
            "amount" => $this->payload['total'],
            "category" => "سفارش سایت",
            "description" => $description
        ]);

    }

}
