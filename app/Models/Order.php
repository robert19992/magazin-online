<?php

namespace App\Models;

use App\Services\IdocGeneratorService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Atributele care pot fi completate în masă.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'supplier_id',
        'order_number',
        'status',
        'total_amount',
        'notes',
        'processed_at',
        'idoc_order_generated',
        'idoc_delivery_generated',
        'idoc_order_generated_at',
        'idoc_delivery_generated_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'idoc_order_generated' => 'boolean',
        'idoc_delivery_generated' => 'boolean',
        'idoc_order_generated_at' => 'datetime',
        'idoc_delivery_generated_at' => 'datetime',
    ];

    /**
     * Statusurile posibile pentru o comandă.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Relația cu clientul care a plasat comanda.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Relația cu furnizorul comenzii.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Relația cu articolele comenzii.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relația cu mesajele IDOC.
     */
    public function idocMessages(): HasMany
    {
        return $this->hasMany(IdocMessage::class);
    }

    /**
     * Relația cu documentele.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Verifică dacă comanda poate fi procesată.
     */
    public function canBeProcessed(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifică dacă comanda poate fi anulată.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, 'processing']);
    }

    /**
     * Verifică dacă comanda poate fi confirmată.
     */
    public function canBeConfirmed(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifică dacă comanda poate fi marcată ca expediată.
     */
    public function canBeShipped(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Verifică dacă comanda poate fi marcată ca livrată.
     */
    public function canBeDelivered(): bool
    {
        return $this->status === self::STATUS_SHIPPED;
    }

    /**
     * Returnează lista de statusuri disponibile.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => __('În așteptare'),
            self::STATUS_CONFIRMED => __('Confirmată'),
            self::STATUS_SHIPPED => __('Expediată'),
            self::STATUS_DELIVERED => __('Livrată'),
            self::STATUS_CANCELLED => __('Anulată'),
        ];
    }

    /**
     * Returnează clasa CSS pentru status.
     */
    public function getStatusClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_CONFIRMED => 'bg-blue-100 text-blue-800',
            self::STATUS_SHIPPED => 'bg-purple-100 text-purple-800',
            self::STATUS_DELIVERED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function calculateTotal()
    {
        $total = $this->items->sum(function ($item) {
            return $item->cantitate * $item->pret_unitar;
        });
        
        $this->total_amount = $total;
        $this->save();
        
        return $total;
    }

    /**
     * Generează documentele pentru comanda plasată.
     * 
     * @param bool $useQueue Folosește coadă pentru procesare asincronă
     * @return array|void Căile către documentele generate sau void dacă se folosește coada
     */
    public function generatePlacedOrderDocuments(bool $useQueue = false)
    {
        if ($useQueue) {
            \App\Jobs\ProcessIdocJob::dispatch($this, 'order');
            return;
        }
        
        $idocGenerator = app(IdocGeneratorService::class);
        return $idocGenerator->generatePlacedOrderDocuments($this);
    }

    /**
     * Generează documentele pentru comanda livrată.
     * 
     * @param bool $useQueue Folosește coadă pentru procesare asincronă
     * @return array|void Căile către documentele generate sau void dacă se folosește coada
     */
    public function generateDeliveredOrderDocuments(bool $useQueue = false)
    {
        if ($useQueue) {
            \App\Jobs\ProcessIdocJob::dispatch($this, 'delivery');
            return;
        }
        
        $idocGenerator = app(IdocGeneratorService::class);
        return $idocGenerator->generateDeliveredOrderDocuments($this);
    }

    /**
     * Marchează comanda ca fiind livrată și generează documentele necesare.
     * 
     * @param bool $useQueue Folosește coadă pentru procesare asincronă
     * @return bool|array
     */
    public function markAsDelivered(bool $useQueue = false)
    {
        if ($this->status === 'activa' || $this->status === self::STATUS_CONFIRMED || $this->status === self::STATUS_SHIPPED) {
            $this->status = 'livrata';
            $this->save();
            
            // Generare documente pentru comanda livrată
            if ($useQueue) {
                \App\Jobs\ProcessIdocJob::dispatch($this, 'delivery');
                return true;
            }
            
            return $this->generateDeliveredOrderDocuments();
        }
        
        return false;
    }

    public function process(): bool
    {
        if (!$this->canBeProcessed()) {
            return false;
        }

        $this->status = 'processing';
        $this->processed_at = now();
        return $this->save();
    }

    public function complete(): bool
    {
        if ($this->status !== 'processing') {
            return false;
        }

        $this->status = 'completed';
        return $this->save();
    }

    public function cancel(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $this->status = 'cancelled';
        return $this->save();
    }

    public function recalculateTotal(): void
    {
        $this->total_amount = $this->items->sum('total_price');
        $this->save();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}