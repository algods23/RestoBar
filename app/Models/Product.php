<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $appends = [
        'image_url',
    ];

    protected $fillable = [
        'category_id',
        'name',
        'barcode',
        'image',
        'price',
        'stock',
        'reorder_level',
        'status',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/'.$this->image);
        }

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400" viewBox="0 0 600 400"><rect width="600" height="400" fill="#f1f5f9"/><rect x="90" y="70" width="420" height="260" rx="28" fill="#e2e8f0"/><circle cx="210" cy="170" r="40" fill="#94a3b8"/><path d="M140 280c34-52 65-78 95-78s61 26 95 78h-190z" fill="#94a3b8"/><path d="M305 280c23-36 47-54 70-54s47 18 70 54H305z" fill="#cbd5e1"/><text x="300" y="360" text-anchor="middle" font-family="Arial, sans-serif" font-size="28" fill="#64748b">No Image</text></svg>');
    }

    public function imageUrl(): string
    {
        return $this->image_url;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->reorder_level;
    }
}
