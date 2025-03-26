<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class UpdateProductStockSeeder extends Seeder
{
    public function run()
    {
        Product::all()->each(function ($product) {
            $product->stock = rand(0, 50); // Випадкові значення
            $product->save();
        });
    }
}
