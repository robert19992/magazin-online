<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'number',
        'order_id',
        'customer_id',
        'supplier_id',
        'file_path',
        'status',
        'total_amount',
        'currency',
        'issue_date',
        'due_date'
    ];

    protected $casts = [
        'issue_date' => 'datetime',
        'due_date' => 'datetime',
        'total_amount' => 'decimal:2'
    ];

    /**
     * Tipurile de documente disponibile
     */
    const TYPE_INVOICE = 'invoice';
    const TYPE_DELIVERY_NOTE = 'delivery_note';

    /**
     * Statusurile posibile pentru documente
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_ISSUED = 'issued';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Relația cu comanda
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relația cu clientul
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Relația cu furnizorul
     */
    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Verifică dacă documentul este o factură
     */
    public function isInvoice()
    {
        return $this->type === self::TYPE_INVOICE;
    }

    /**
     * Verifică dacă documentul este un aviz de livrare
     */
    public function isDeliveryNote()
    {
        return $this->type === self::TYPE_DELIVERY_NOTE;
    }

    /**
     * Verifică dacă documentul poate fi anulat
     */
    public function canBeCancelled()
    {
        return $this->status !== self::STATUS_CANCELLED && 
               $this->status !== self::STATUS_PAID;
    }

    /**
     * Verifică dacă documentul este plătit
     */
    public function isPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Verifică dacă documentul este anulat
     */
    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Verifică dacă documentul este în draft
     */
    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Verifică dacă documentul este emis
     */
    public function isIssued()
    {
        return $this->status === self::STATUS_ISSUED;
    }

    /**
     * Returnează calea către fișierul PDF
     */
    public function getFilePath()
    {
        return $this->file_path;
    }

    /**
     * Returnează numele documentului pentru afișare
     */
    public function getDisplayName()
    {
        return $this->isInvoice() ? 'Factura' : 'Aviz de livrare';
    }

    /**
     * Returnează statusul pentru afișare
     */
    public function getDisplayStatus()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Ciornă',
            self::STATUS_ISSUED => 'Emis',
            self::STATUS_PAID => 'Plătit',
            self::STATUS_CANCELLED => 'Anulat',
            default => 'Necunoscut'
        };
    }

    /**
     * Returnează clasa CSS pentru status
     */
    public function getStatusClass()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::STATUS_ISSUED => 'bg-blue-100 text-blue-800',
            self::STATUS_PAID => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
} 