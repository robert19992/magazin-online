<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numar_factura',
        'order_id',
        'supplier_id',
        'customer_id',
        'subtotal',
        'tva',
        'total',
        'status',
        'data_emitere',
        'data_scadenta',
        'mentiuni'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tva' => 'decimal:2',
        'total' => 'decimal:2',
        'data_emitere' => 'date',
        'data_scadenta' => 'date'
    ];

    // Relații
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // Scopuri
    public function scopeForSupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Metode helper
    public function calculateTotals(): void
    {
        $this->subtotal = $this->order->total;
        $this->tva = $this->subtotal * 0.19; // 19% TVA
        $this->total = $this->subtotal + $this->tva;
    }

    public function markAsPaid(): void
    {
        $this->update(['status' => 'platita']);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => 'anulata']);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'emisa' && now()->greaterThan($this->data_scadenta);
    }

    public function getRemainingDays(): int
    {
        return now()->diffInDays($this->data_scadenta, false);
    }
} 