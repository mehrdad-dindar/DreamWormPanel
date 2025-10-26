<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWooProductUpdate;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    public function updateWebhooks(Request $request)
    {
        $secret = config('services.woocommerce.webhook_secret') ?? env('WC_WEBHOOK_SECRET');
        $payload = $request->getContent();
        $signatureHeader = $request->header('X-Wc-Webhook-Signature');

//        if (! verifySignature($payload, $signatureHeader, $secret)) {
//            Log::warning('WooCommerce webhook signature mismatch', ['headers' => $request->headers->all()]);
//            return response()->json(['message' => 'Invalid signature'], 401);
//        }

        $topic = $request->header('X-WC-Webhook-Topic') ?? $request->input('topic');
        Log::info('Received WooCommerce webhook', ['topic' => $topic]);
        if (!is_null($topic)) {
            ProcessWooProductUpdate::dispatch($topic, json_decode($payload, true));
            return response()->json(['received' => true], 200);
        }
        return response()->json(['bit' => true], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
