<?php

namespace App\Events\Products;

use App\Models\Product;
use Illuminate\Foundation\Events\Dispatchable;

class ProductCreated
{
    use Dispatchable;

    public function __construct(public Product $product) {}
}