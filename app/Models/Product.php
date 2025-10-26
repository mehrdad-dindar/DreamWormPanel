<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $guarded;

    protected function helperText(): Attribute
    {
        $title =  ' هر کیلوگرم ' . number_format($this->price) . ' تومان';
        return Attribute::make(
            get: fn () => $title,
        );
    }
}
