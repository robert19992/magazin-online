<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; // Toți utilizatorii autentificați pot vedea facturile lor
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->id === $invoice->supplier_id || $user->id === $invoice->customer_id;
    }

    public function create(User $user): bool
    {
        return $user->isSupplier(); // Doar furnizorii pot crea facturi
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->id === $invoice->supplier_id && $invoice->status === 'emisa';
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return false; // Facturile nu pot fi șterse, doar anulate
    }

    public function download(User $user, Invoice $invoice): bool
    {
        return $user->id === $invoice->supplier_id || $user->id === $invoice->customer_id;
    }
} 