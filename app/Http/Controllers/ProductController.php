<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Product::class);

        return view('products.index');
    }

    public function show(Product $product): View
    {
        Gate::authorize('view', $product);

        return view('products.show', ['product' => $product]);
    }
}