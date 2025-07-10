<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $guarded;

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getOrderItems()
    {
        $items = [];
        foreach ($this->items as $item) {
            if ($item->quantity < 1) {
                array_push($items, $item->quantity * 1000 . ' گرم ' . $item->product?->name);
            }else {
                array_push($items, $item->quantity . ' کیلوگرم ' . $item->product?->name);
            }
        }
        return $items;
    }
}
