<?php

namespace App\Services\Divisions;

use App\Enums\CustomerType;
use App\Enums\Division;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Services\Ai\DemandForecastService;

class DivisionMetricsService
{
    public function __construct(private DemandForecastService $demand) {}

    public function metrics(Division $division): array
    {
        $orders = SalesOrder::query()
            ->where('division', $division->value)
            ->where('status', '!=', 'cancelled');

        return [
            'revenue' => (float) (clone $orders)->sum('total'),
            'orders' => (clone $orders)->count(),
            'open_leads' => Lead::query()->where('division', $division->value)->open()->count(),
            'pipeline' => (float) Lead::query()->where('division', $division->value)->open()->sum('estimated_value'),
            'outstanding' => (float) Invoice::query()->where('division', $division->value)->outstanding()->sum('balance_due'),
            'customers' => Customer::query()->where('division', $division->value)->count(),
        ];
    }

    /** Revenue per product category (Engine Oil, Grease, Hydraulic Oil, Coolant…). */
    public function categoryBreakdown(Division $division): array
    {
        return SalesOrderItem::query()
            ->with('product.category')
            ->whereHas('product', fn ($q) => $q->where('division', $division->value))
            ->whereHas('order', fn ($q) => $q->where('division', $division->value)->where('status', '!=', 'cancelled'))
            ->get()
            ->groupBy(fn (SalesOrderItem $i) => $i->product?->category?->name ?? 'Uncategorised')
            ->map(fn ($group) => round((float) $group->sum('line_total'), 2))
            ->sortDesc()
            ->all();
    }

    /** @return array<int, array{name: string, sku: string, revenue: float, qty: int}> */
    public function topProducts(Division $division, int $limit = 5): array
    {
        return SalesOrderItem::query()
            ->with('product')
            ->whereHas('product', fn ($q) => $q->where('division', $division->value))
            ->whereHas('order', fn ($q) => $q->where('division', $division->value)->where('status', '!=', 'cancelled'))
            ->get()
            ->groupBy('product_id')
            ->map(fn ($group) => [
                'name' => $group->first()->product?->name ?? $group->first()->description,
                'sku' => $group->first()->product?->sku ?? '—',
                'revenue' => round((float) $group->sum('line_total'), 2),
                'qty' => (int) $group->sum('quantity'),
            ])
            ->sortByDesc('revenue')
            ->take($limit)
            ->values()
            ->all();
    }

    /** @return array<int, array{name: string, type: string, revenue: float, orders: int}> */
    public function topCustomers(Division $division, int $limit = 5): array
    {
        return SalesOrder::query()
            ->with('customer')
            ->where('division', $division->value)
            ->where('status', '!=', 'cancelled')
            ->get()
            ->groupBy('customer_id')
            ->map(fn ($group) => [
                'name' => $group->first()->customer?->displayName() ?? '—',
                'type' => $group->first()->customer?->type()->label() ?? '—',
                'revenue' => round((float) $group->sum('total'), 2),
                'orders' => $group->count(),
            ])
            ->sortByDesc('revenue')
            ->take($limit)
            ->values()
            ->all();
    }

    /** Channel partners (distributors / dealers / garages) with performance + dormancy flag. */
    public function partners(Division $division): array
    {
        $partnerTypes = [CustomerType::Distributor->value, CustomerType::Dealer->value, CustomerType::Garage->value];

        return Customer::query()
            ->where('division', $division->value)
            ->whereIn('type', $partnerTypes)
            ->get()
            ->map(function (Customer $partner) use ($division) {
                $orders = SalesOrder::query()
                    ->where('customer_id', $partner->id)
                    ->where('division', $division->value)
                    ->where('status', '!=', 'cancelled');

                $lastOrder = (clone $orders)->latest()->first();

                return [
                    'id' => $partner->id,
                    'name' => $partner->displayName(),
                    'type' => $partner->type()->label(),
                    'revenue' => round((float) (clone $orders)->sum('total'), 2),
                    'orders' => (clone $orders)->count(),
                    'dormant' => $lastOrder === null || $lastOrder->created_at->lt(now()->subDays(90)),
                ];
            })
            ->sortByDesc('revenue')
            ->values()
            ->all();
    }

    /** AI reorder plan: suggested production/purchase quantities for active SKUs. */
    public function reorderPlan(Division $division): array
    {
        return Product::query()
            ->active()
            ->where('division', $division->value)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
            ] + $this->demand->forProduct($product))
            ->filter(fn ($row) => $row['suggested_order'] > 0)
            ->sortByDesc('suggested_order')
            ->values()
            ->all();
    }
}