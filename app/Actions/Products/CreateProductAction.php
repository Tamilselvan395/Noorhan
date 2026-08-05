<?php

namespace App\Actions\Products;

use App\DTOs\Products\ProductDTO;
use App\Events\Products\ProductCreated;
use App\Models\Product;

class CreateProductAction
{
    public function execute(ProductDTO $dto): Product
    {
        $product = Product::create($dto->toArray());

        $product->logActivity('created the product');

        event(new ProductCreated($product));

        return $product;
    }
}