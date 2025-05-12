<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdocMessage extends Model
{
    use HasFactory;

    /**
     * Atributele care pot fi completate în masă.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'client_id',
        'supplier_id',
        'message_type',
        'direction',
        'file_path',
        'content',
        'status',
        'processed_at',
    ];

    /**
     * Atributele care trebuie convertite.
     *
     * @var array
     */
    protected $casts = [
        'processed_at' => 'datetime',
    ];

    /**
     * Tipurile de mesaje IDOC
     */
    const TYPE_ORDER = 'order';
    const TYPE_DELIVERY = 'delivery';

    /**
     * Direcțiile mesajelor IDOC
     */
    const DIRECTION_CLIENT_TO_SUPPLIER = 'client_to_supplier';
    const DIRECTION_SUPPLIER_TO_CLIENT = 'supplier_to_client';

    /**
     * Statusurile mesajelor IDOC
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSED = 'processed';
    const STATUS_FAILED = 'failed';

    /**
     * Relația cu comanda.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relația cu furnizorul.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Relația cu clientul.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Returnează calea către fișierul IDOC.
     */
    public function getFilePath(): string
    {
        return $this->file_path;
    }

    /**
     * Marchează mesajul ca fiind procesat.
     */
    public function markAsProcessed(): bool
    {
        $this->status = self::STATUS_PROCESSED;
        $this->processed_at = now();
        return $this->save();
    }

    /**
     * Marchează mesajul ca având erori.
     */
    public function markAsFailed(): bool
    {
        $this->status = self::STATUS_FAILED;
        $this->processed_at = now();
        return $this->save();
    }

    /**
     * Returnează tipul mesajului pentru afișare.
     */
    public function getDisplayType(): string
    {
        return match($this->message_type) {
            self::TYPE_ORDER => 'Comandă',
            self::TYPE_DELIVERY => 'Livrare',
            default => 'Necunoscut'
        };
    }

    /**
     * Returnează direcția mesajului pentru afișare.
     */
    public function getDisplayDirection(): string
    {
        return match($this->direction) {
            self::DIRECTION_CLIENT_TO_SUPPLIER => 'Client → Furnizor',
            self::DIRECTION_SUPPLIER_TO_CLIENT => 'Furnizor → Client',
            default => 'Necunoscut'
        };
    }

    /**
     * Returnează statusul mesajului pentru afișare.
     */
    public function getDisplayStatus(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'În așteptare',
            self::STATUS_PROCESSED => 'Procesat',
            self::STATUS_FAILED => 'Eroare',
            default => 'Necunoscut'
        };
    }

    /**
     * Returnează clasa CSS pentru status.
     */
    public function getStatusClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_PROCESSED => 'bg-green-100 text-green-800',
            self::STATUS_FAILED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
} 