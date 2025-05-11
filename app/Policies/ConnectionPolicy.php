<?php

namespace App\Policies;

use App\Models\Connection;
use App\Models\User;

class ConnectionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Connection $connection): bool
    {
        if ($user->isClient()) {
            return $connection->client_id === $user->id;
        }

        return $connection->supplier_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Connection $connection): bool
    {
        // Doar furnizorii pot actualiza statusul conexiunii
        if (!$user->isSupplier()) {
            return false;
        }

        // Furnizorul poate actualiza doar conexiunile sale
        return $connection->supplier_id === $user->id;
    }

    public function delete(User $user, Connection $connection): bool
    {
        if ($user->isClient()) {
            return $connection->client_id === $user->id;
        }

        return $connection->supplier_id === $user->id;
    }
} 