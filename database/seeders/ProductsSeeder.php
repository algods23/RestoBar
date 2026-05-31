<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            Category::firstOrCreate(['name' => 'Food'], ['description' => 'Food items']),
            Category::firstOrCreate(['name' => 'Drinks'], ['description' => 'Non-alcoholic drinks']),
            Category::firstOrCreate(['name' => 'Alcohol'], ['description' => 'Alcoholic beverages']),
        ];

        $samples = [
            'Cheeseburger','Hamburger','Chicken Sandwich','Fries','Onion Rings',
            'Caesar Salad','Pasta Carbonara','Grilled Fish','Steak','Pancakes',
            'Iced Tea','Coffee','Lemonade','Soda','Milkshake',
            'Beer Bottle','Red Wine','White Wine','Whiskey','Rum'
        ];

        foreach ($samples as $i => $name) {
            $category = $categories[$i % count($categories)];

            Product::firstOrCreate(
                ['name' => $name],
                [
                    'category_id' => $category->id,
                    'barcode' => (string) random_int(1000000000, 9999999999),
                    'price' => rand(50, 500),
                    'stock' => rand(10, 200),
                    'reorder_level' => rand(5, 20),
                    'status' => 'available',
                    'description' => $name . ' - sample product for testing',
                ]
            );
        }
    }
}
