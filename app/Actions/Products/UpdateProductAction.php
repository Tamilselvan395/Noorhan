<?php

namespace App\Actions\Products;

use App\DTOs\Products\ProductDTO;
use App\Events\Products\ProductUpdated;
use App\Models\Product;

class UpdateProductAction
{
    public function execute(Product $product, ProductDTO $dto): void
    {
        $product->update($dto->toArray());

        $product->logActivity('updated the product');

        event(new ProductUpdated($product));
    }
}