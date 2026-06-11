<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $fillable = [
        'number',
        'is_occupied',
        'current_order_id',
    ];

    protected $casts = [
        'is_occupied' => 'boolean',
    ];

    public function currentOrder()
    {
        return $this->belongsTo(Order::class, 'current_order_id');
    }
}