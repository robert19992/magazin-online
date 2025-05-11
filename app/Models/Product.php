<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'name',
        'description',
        'price',
        'stock',
        'sku',
        'category',
        'specifications',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'specifications' => 'array',
        'is_active' => 'boolean',
    ];

    // Relații
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopuri
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Metode helper
    public function hasStock(int $quantity): bool
    {
        return $this->stock >= $quantity;
    }

    public function updateStock(int $quantity): bool
    {
        if ($this->stock + $quantity < 0) {
            return false;
        }

        $this->stock += $quantity;
        return $this->save();
    }

    public function getTotalValue(): float
    {
        return $this->stock * $this->price;
    }

    public function isLowStock(): bool
    {
        return $this->stock < 10;
    }
}
