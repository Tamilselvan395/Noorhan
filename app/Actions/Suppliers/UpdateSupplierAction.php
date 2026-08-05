<?php

namespace App\Actions\Suppliers;

use App\DTOs\Suppliers\SupplierDTO;
use App\Events\Suppliers\SupplierUpdated;
use App\Models\Supplier;

class UpdateSupplierAction
{
    public function execute(Supplier $supplier, SupplierDTO $dto): void
    {
        $supplier->update($dto->toArray());

        $supplier->logActivity('updated the supplier record');

        event(new SupplierUpdated($supplier));
    }
}