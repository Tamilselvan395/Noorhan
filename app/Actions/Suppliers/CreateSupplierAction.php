<?php

namespace App\Actions\Suppliers;

use App\DTOs\Suppliers\SupplierDTO;
use App\Events\Suppliers\SupplierCreated;
use App\Models\Supplier;
use App\Models\User;

class CreateSupplierAction
{
    public function execute(SupplierDTO $dto, ?User $creator = null): Supplier
    {
        $supplier = Supplier::create($dto->toArray());

        $supplier->logActivity('created the supplier record');

        event(new SupplierCreated($supplier));

        return $supplier;
    }
}