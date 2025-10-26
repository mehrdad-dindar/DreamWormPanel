<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWooCustomerCreate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
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
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    public function storeWebhooks(Request $request)
    {
        $payload = $request->getContent();
        $topic = $request->header('X-WC-Webhook-Topic') ?? $request->input('topic');
        if (!is_null($topic)) {
            ProcessWooCustomerCreate::dispatch($topic, json_decode($payload, true, 512, JSON_THROW_ON_ERROR));
            return response()->json(['received' => true]);
        }
        return response()->json(['bit' => true], 200);


    }
}
