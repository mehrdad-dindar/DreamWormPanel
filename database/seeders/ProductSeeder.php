<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'کرم زنده میلورم',
                'price' => 250000,
            ],[
                'name' => 'کرم خشک میلورم',
                'price' => 500000,
            ],[
                'name' => 'کود میلورم',
                'price' => 10000,
            ],[
                'name' => 'سوسک مولد زنده',
                'price' => 4500000,
            ]
        ];

        Product::insert($products);
    }
}
