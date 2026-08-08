<?php

namespace App\Policies;

use App\Models\SupplierEnquiry;
use App\Models\User;

class SupplierEnquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SupplierEnquiry $enquiry): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, SupplierEnquiry $enquiry): bool
    {
        return true; // role matrix (Sales Exec / Managers) arrives with Administration module
    }
}