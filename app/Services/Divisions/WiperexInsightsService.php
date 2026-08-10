<?php

namespace App\Services\Divisions;

use App\Models\Product;
use App\Models\SalesOrderItem;
use Illuminate\Support\Str;

class WiperexInsightsService
{
    private const DIVISION = 'wiperex';
    private const CONSUMABLE_CATEGORIES = ['Cleaning Liquid', 'Car Sponge'];

    /** Revenue by wiper blade size (fitment mix) from product attributes. */
    public function sizeMix(): array
    {
        $sizes = Product::query()->where('division', self::DIVISION)->get()
            ->filter(fn (Product $p) => ! empty($p->attributes['size']))
            ->mapWithKeys(fn (Product $p) => [$p->id => $p->attributes['size']]);

        if ($sizes->isEmpty()) {
            return [];
        }

        return SalesOrderItem::query()
            ->whereIn('product_id', $sizes->keys())
            ->whereHas('order', fn ($q) => $q->where('division', self::DIVISION)->where('status', '!=', 'cancelled'))
            ->get()
            ->groupBy(fn (SalesOrderItem $i) => $sizes[$i->product_id])
            ->map(fn ($group) => round((float) $group->sum('line_total'), 2))
            ->sortDesc()
            ->all();
    }

    /** Customers with repeat consumable purchases → standing-order prospects. */
    public function replenishmentCandidates(): array
    {
        $consumables = Product::query()
            ->where('division', self::DIVISION)
            ->whereHas('category', fn ($q) => $q->whereIn('name', self::CONSUMABLE_CATEGORIES))
            ->pluck('id');

        if ($consumables->isEmpty()) {
            $consumables = Product::query()->where('division', self::DIVISION)->pluck('id');
        }

        return SalesOrderItem::query()
            ->with('order.customer')
            ->whereIn('product_id', $consumables)
            ->whereHas('order', fn ($q) => $q->where('division', self::DIVISION)->where('status', '!=', 'cancelled'))
            ->get()
            ->groupBy(fn (SalesOrderItem $i) => $i->order->customer_id)
            ->map(function ($group) {
                $orders = $group->pluck('order')->unique('id');
                $last = $orders->max('created_at');

                return [
                    'customer_id' => $orders->first()->customer_id,
                    'name' => $orders->first()->customer?->displayName() ?? '—',
                    'orders' => $orders->count(),
                    'last_order' => $last?->diffForHumans(),
                    'eligible' => $orders->count() >= 2 && $last !== null && $last->gte(now()->subDays(120)),
                ];
            })
            ->filter(fn ($row) => $row['eligible'])
            ->sortByDesc('orders')
            ->values()
            ->all();
    }

    /** Season-aware focus recommendation for the Wiperex catalog. */
    public function seasonalSuggestion(): array
    {
        $month = now()->month;

        return match (true) {
            in_array($month, [10, 11, 12, 1, 2], true) => [
                'season' => 'Wet Season',
                'focus' => 'Wiper Blades',
                'message' => 'Peak wiper demand window — launch a blade campaign and stock sizes 16"–26".',
            ],
            in_array($month, [6, 7, 8], true) => [
                'season' => 'Dust Season',
                'focus' => 'Cleaning Liquid & Sponge',
                'message' => 'High dust conditions — push cleaning liquid & sponge bundles to garages and shops.',
            ],
            default => [
                'season' => 'Shoulder Season',
                'focus' => 'Bundles',
                'message' => 'Run bundle promotions (blade + liquid) to keep volume steady.',
            ],
        };
    }

    public function draftCampaignName(): string
    {
        return 'Wiperex '.$this->seasonalSuggestion()['season'].' '.now()->year;
    }

    public function draftCampaignUtm(): string
    {
        return Str::slug($this->draftCampaignName());
    }
}