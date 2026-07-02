<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')
            ->orderBy('id')
            ->chunkById(100, function ($orders) {
                foreach ($orders as $order) {
                    $subtotal = (float) $order->subtotal;
                    $discountAmount = (float) $order->discount_amount;
                    $discountPercentage = $subtotal > 0
                        ? min(100, round(($discountAmount / $subtotal) * 100, 2))
                        : 0;

                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update([
                            'discount_percentage' => $discountPercentage,
                            'vat_amount' => 0,
                            'total_amount' => max(0, round($subtotal - $discountAmount, 2)),
                        ]);
                }
            });
    }

    public function down(): void
    {
        //
    }
};
