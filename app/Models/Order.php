<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    // Constante
    const STATUS_PENDING = 'pending';     // Comandă în așteptare
    const STATUS_ACTIVE = 'active';       // Comandă activă (procesată de furnizor)
    const STATUS_DELIVERED = 'delivered'; // Comandă livrată
    const STATUS_CANCELLED = 'cancelled'; // Comandă anulată

    /**
     * Atributele care pot fi completate în masă
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'supplier_id',
        'order_number',
        'status',
        'notes',
        'total_amount',
    ];

    /**
     * Atribute care ar trebui convertite la tipuri native
     *
     * @var array
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obține clientul asociat acestei comenzi
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Obține furnizorul asociat acestei comenzi
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Obține articolele din această comandă
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Actualizează starea comenzii la "activă"
     */
    public function activate(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }

    /**
     * Marchează comanda ca livrată
     */
    public function markAsDelivered(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        $this->status = self::STATUS_DELIVERED;
        return $this->save();
    }

    /**
     * Anulează comanda
     */
    public function cancel(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        return $this->save();
    }

    /**
     * Verifică dacă comanda poate fi anulată
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_ACTIVE]);
    }

    /**
     * Recalculează totalul comenzii pe baza articolelor
     */
    public function recalculateTotal(): bool
    {
        $this->total_amount = $this->items()->sum('total_price');
        return $this->save();
    }

    /**
     * Obține culoarea de fundal pentru status în interfață
     */
    public function getStatusColorClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_ACTIVE => 'bg-blue-100 text-blue-800',
            self::STATUS_DELIVERED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Obține textul tradus pentru status
     */
    public function getStatusText(): string
    {
        $statuses = [
            self::STATUS_PENDING => __('În așteptare'),
            self::STATUS_ACTIVE => __('Activă'),
            self::STATUS_DELIVERED => __('Livrată'),
            self::STATUS_CANCELLED => __('Anulată'),
        ];

        return $statuses[$this->status] ?? $this->status;
    }
}