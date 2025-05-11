<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_name',
        'street',
        'street_number',
        'cui',
        'connect_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relații
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function hasRole($role)
    {
        if (str_contains($role, ',')) {
            $roles = explode(',', $role);
            return in_array($this->role, $roles);
        }
        return $this->role === $role;
    }

    public function isSupplier(): bool
    {
        return $this->role === 'supplier';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    // Relații pentru furnizor
    public function products()
    {
        return $this->hasMany(Product::class, 'supplier_id');
    }

    public function supplierOrders()
    {
        return $this->hasMany(Order::class, 'supplier_id');
    }

    public function supplierConnections()
    {
        return $this->hasMany(Connection::class, 'supplier_id');
    }

    // Relații pentru client
    public function clientOrders()
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    public function clientConnections()
    {
        return $this->hasMany(Connection::class, 'client_id');
    }

    // Metode helper
    public function getFullAddressAttribute(): string
    {
        return "{$this->street} {$this->street_number}";
    }
}
