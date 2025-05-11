<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Atributele care pot fi completate în masă.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'unit',
        'confirmed_quantity',
        'notes',
        'status',
        'subtotal'
    ];

    /**
     * Atributele care ar trebui convertite.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'integer',
        'confirmed_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    /**
     * Relația cu comanda.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relația cu produsul.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculează subtotalul elementului.
     */
    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }

    // Metode
    public function calculateTotal(): void
    {
        $this->total_price = $this->quantity * $this->unit_price;
        $this->save();
    }

    public function updateConfirmedQuantity($quantity)
    {
        $this->confirmed_quantity = $quantity;
        if ($quantity === 0) {
            $this->status = 'rejected';
        } elseif ($quantity < $this->quantity) {
            $this->status = 'partial';
        } else {
            $this->status = 'confirmed';
        }
        $this->save();
    }

    // Scopuri
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->subtotal = $item->quantity * $item->unit_price;
            $item->calculateTotal();
        });

        static::updating(function ($item) {
            $item->subtotal = $item->quantity * $item->unit_price;
            $item->calculateTotal();
        });

        static::saved(function ($item) {
            $item->order->recalculateTotal();
        });
    }
}
