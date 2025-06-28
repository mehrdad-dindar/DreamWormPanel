<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    protected $guarded;

    protected function helperText(): Attribute
    {
        $title =  ' هر کیلوگرم ' . number_format($this->price) . ' تومان';
        return Attribute::make(
            get: fn () => $title,
        );
    }
}
