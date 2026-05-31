<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $user = User::where('role', 'cashier')->first() ?? User::first();

            $p1 = Product::where('name', 'Cheeseburger')->first();
            $p2 = Product::where('name', 'Iced Tea')->first();
            $p3 = Product::where('name', 'Beer Bottle')->first();

            if (! $p1 || ! $p2 || ! $p3) {
                return; // products not seeded
            }

            // Order 1 - completed
            $order1 = Order::create([
                'user_id' => $user->id,
                'order_type' => 'dine_in',
                'subtotal' => 360,
                'discount_amount' => 0,
                'vat_amount' => 36,
                'total_amount' => 396,
                'status' => Order::STATUS_COMPLETED,
                'payment_method' => 'cash',
                'notes' => 'Test order - completed',
            ]);

            OrderItem::create([
                'order_id' => $order1->id,
                'product_id' => $p1->id,
                'quantity' => 2,
                'price' => $p1->price,
                'subtotal' => 2 * $p1->price,
            ]);

            OrderItem::create([
                'order_id' => $order1->id,
                'product_id' => $p2->id,
                'quantity' => 1,
                'price' => $p2->price,
                'subtotal' => 1 * $p2->price,
            ]);

            // Adjust product stocks and create inventory records
            $this->decrementStock($p1, $user->id, $order1->id, 2, 'stock_out');
            $this->decrementStock($p2, $user->id, $order1->id, 1, 'stock_out');

            // Order 2 - pending
            $order2 = Order::create([
                'user_id' => $user->id,
                'order_type' => 'takeout',
                'subtotal' => 120,
                'discount_amount' => 0,
                'vat_amount' => 12,
                'total_amount' => 132,
                'status' => Order::STATUS_PENDING,
                'payment_method' => 'card',
                'notes' => 'Test order - pending',
            ]);

            OrderItem::create([
                'order_id' => $order2->id,
                'product_id' => $p3->id,
                'quantity' => 1,
                'price' => $p3->price,
                'subtotal' => 1 * $p3->price,
            ]);

            $this->decrementStock($p3, $user->id, $order2->id, 1, 'stock_out');
        });
    }

    private function decrementStock(Product $product, int $userId, int $orderId, int $quantity, string $type)
    {
        $previous = $product->stock ?? 0;
        $new = max(0, $previous - $quantity);

        Inventory::create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'order_id' => $orderId,
            'type' => $type,
            'quantity' => $quantity,
            'previous_stock' => $previous,
            'new_stock' => $new,
            'notes' => 'Automated test seed inventory change',
        ]);

        $product->stock = $new;
        $product->save();
    }
}
