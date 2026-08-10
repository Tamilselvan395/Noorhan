<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function download(User $user, Document $document): bool
    {
        return true; // authenticated staff; role matrix arrives with data-classification policy
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->hasRole(['Super Admin']) || $document->uploaded_by === $user->id;
    }
}