<?php

namespace App\Actions\Suppliers;

use App\Models\Supplier;
use App\Models\SupplierRating;
use App\Models\User;

class RateSupplierAction
{
    public function execute(Supplier $supplier, User $user, int $quality, int $price, int $delivery, int $service, ?string $comment = null): SupplierRating
    {
        $rating = $supplier->ratings()->create([
            'user_id' => $user->id,
            'quality' => $quality,
            'price' => $price,
            'delivery' => $delivery,
            'service' => $service,
            'comment' => $comment,
        ]);

        $supplier->logActivity("rated the supplier {$rating->overall()}/5");

        return $rating;
    }
}