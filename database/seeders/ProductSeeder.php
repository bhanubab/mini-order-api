<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Laptop',       'description' => 'Gaming laptop 16GB RAM',  'price' => 999.99,  'stock' => 10],
            ['name' => 'Mouse',        'description' => 'Wireless optical mouse',   'price' => 29.99,   'stock' => 50],
            ['name' => 'Keyboard',     'description' => 'Mechanical RGB keyboard',  'price' => 79.99,   'stock' => 30],
            ['name' => 'Monitor',      'description' => '27 inch 4K display',       'price' => 399.99,  'stock' => 15],
            ['name' => 'Headphones',   'description' => 'Noise cancelling',         'price' => 149.99,  'stock' => 25],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}