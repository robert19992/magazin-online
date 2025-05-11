<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Connection extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'client_id',
        'status',
        'connected_at',
        'disconnected_at',
        'notes',
    ];

    protected $casts = [
        'connected_at' => 'datetime',
        'disconnected_at' => 'datetime',
    ];

    // Relații
    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    // Helpers
    public function activate(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'active';
        $this->connected_at = now();
        return $this->save();
    }

    public function deactivate(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $this->status = 'inactive';
        $this->disconnected_at = now();
        return $this->save();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // Metode helper
    public static function connect($clientId, $furnizorId)
    {
        return static::firstOrCreate([
            'client_id' => $clientId,
            'furnizor_id' => $furnizorId
        ]);
    }

    public static function disconnect($clientId, $furnizorId)
    {
        return static::where('client_id', $clientId)
            ->where('furnizor_id', $furnizorId)
            ->delete();
    }

    public function scopeForSupplier($query, $supplierId)
    {
        return $query->where('furnizor_id', $supplierId);
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('client_id', $customerId);
    }
}
