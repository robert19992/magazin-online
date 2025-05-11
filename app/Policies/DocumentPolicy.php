<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Document $document)
    {
        return $user->id === $document->customer_id;
    }

    public function download(User $user, Document $document)
    {
        return $user->id === $document->customer_id;
    }
} 