<?php

namespace App\Services\Ai;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrderItem;
use Illuminate\Support\Collection;

class RecommendationService
{
    /** @return Collection<int, array{product: Product, reason: string}> */
    public function forCustomer(Customer $customer, int $limit = 5): Collection
    {
        $bought = $customer->orders()->with('items')->get()
            ->flatMap(fn ($o) => $o->items->pluck('product_id')->filter())->unique()->values();

        $recommendations = collect();

        if ($bought->isNotEmpty()) {
            // Co-occurrence: products ordered together with what this customer buys
            $coCounts = SalesOrderItem::query()
                ->whereNotNull('product_id')
                ->whereNotIn('product_id', $bought)
                ->whereIn('sales_order_id',
                    SalesOrderItem::whereIn('product_id', $bought)->pluck('sales_order_id')->unique())
                ->get()
                ->groupBy('product_id')
                ->map(fn ($group) => $group->sum('quantity'))
                ->sortDesc();

            foreach ($coCounts->take($limit) as $productId => $qty) {
                $product = Product::find($productId);
                if ($product) {
                    $recommendations->push(['product' => $product, 'reason' => "Frequently bought with your usual items ({$qty}× co-ordered)"]);
                }
            }
        }

        if ($recommendations->count() < $limit) {
            // Catalog gaps: best sellers in the customer's division they never bought
            Product::query()->active()->where('division', $customer->division)
                ->whereNotIn('id', $bought->merge($recommendations->pluck('product.id')))
                ->limit($limit - $recommendations->count())
                ->get()
                ->each(fn (Product $p) => $recommendations->push(['product' => $p, 'reason' => 'Top catalog item in your division']));
        }

        return $recommendations->take($limit)->values();
    }
}