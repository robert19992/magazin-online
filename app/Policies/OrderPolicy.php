<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->isClient()) {
            return $order->client_id === $user->id;
        }

        return $order->supplier_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isClient();
    }

    public function update(User $user, Order $order): bool
    {
        if ($user->isSupplier()) {
            return $order->supplier_id === $user->id;
        }
        
        if ($user->isClient()) {
            return $order->client_id === $user->id && in_array($order->status, ['pending', 'active']);
        }
        
        return false;
    }

    public function delete(User $user, Order $order): bool
    {
        return false; // Nu permitem ștergerea comenzilor
    }

    public function export(User $user): bool
    {
        return $user->isSupplier();
    }
} 