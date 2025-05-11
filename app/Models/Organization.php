<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'teccom_id',
        'address',
        'city',
        'postal_code',
        'country',
        'phone',
        'email',
        'vat_number',
        'registration_number',
        'type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relații
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function customerOrders()
    {
        return $this->hasMany(Order::class, 'customer_organization_id');
    }

    public function supplierOrders()
    {
        return $this->hasMany(Order::class, 'supplier_organization_id');
    }

    // Scopuri
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSuppliers($query)
    {
        return $query->where('type', 'supplier');
    }

    public function scopeCustomers($query)
    {
        return $query->where('type', 'customer');
    }
}
