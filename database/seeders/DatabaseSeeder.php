<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@restobar.test'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'cashier@restobar.test'],
            [
                'name' => 'Cashier User',
                'password' => Hash::make('password'),
                'role' => 'cashier',
            ]
        );

        $food = Category::firstOrCreate(['name' => 'Food'], ['description' => 'Food items']);
        $drinks = Category::firstOrCreate(['name' => 'Drinks'], ['description' => 'Non-alcoholic drinks']);
        $alcohol = Category::firstOrCreate(['name' => 'Alcohol'], ['description' => 'Alcoholic beverages']);

        Product::firstOrCreate(
            ['name' => 'Cheeseburger'],
            ['category_id' => $food->id, 'price' => 150, 'stock' => 50, 'reorder_level' => 10, 'status' => 'available']
        );

        Product::firstOrCreate(
            ['name' => 'Iced Tea'],
            ['category_id' => $drinks->id, 'price' => 60, 'stock' => 80, 'reorder_level' => 15, 'status' => 'available']
        );

        Product::firstOrCreate(
            ['name' => 'Beer Bottle'],
            ['category_id' => $alcohol->id, 'price' => 120, 'stock' => 40, 'reorder_level' => 8, 'status' => 'available']
        );
    }
}
