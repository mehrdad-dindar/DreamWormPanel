<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $guarded;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
