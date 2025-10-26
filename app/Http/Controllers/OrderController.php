<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWooOrderCreate;
use App\Models\Order;
use Illuminate\Http\Request;
use JsonException;

class OrderController extends Controller
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
     * @throws JsonException
     */
    public function storeWebhooks(Request $request)
    {
        $payload = $request->getContent();
        $topic = $request->header('X-Wc-Webhook-Topic') ?? $request->input('topic');
        if (!is_null($topic)) {
            ProcessWooOrderCreate::dispatch($topic, json_decode($payload, true, 512, JSON_THROW_ON_ERROR));
            return response()->json(['received' => true]);
        }
        return response()->json(['bit' => true], 200);    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
