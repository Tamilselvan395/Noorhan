<?php

namespace App\Services\Ai;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use Illuminate\Support\Str;

class NaturalLanguageSearchService
{
    /** Parse plain English into structured CRM queries. */
    public function search(string $query): array
    {
        $q = Str::lower($query);
        $results = ['leads' => [], 'customers' => [], 'invoices' => [], 'parsed' => []];

        $division = collect(['automotive', 'swiftec', 'wiperex', 'otozaar'])->first(fn ($d) => Str::contains($q, $d));
        $source = collect(['facebook', 'instagram', 'whatsapp', 'website', 'exhibition', 'walk'])->first(fn ($s) => Str::contains($q, $s));
        $above = null;
        if (preg_match('/(above|over|more than)\s+(\d+)/', $q, $m)) $above = (float) $m[2];

        if (Str::contains($q, ['lead', 'leads'])) {
            $leads = Lead::query()
                ->when($division, fn ($x) => $x->where('division', $division))
                ->when($source, fn ($x) => $x->where('source', 'like', "%{$source}%"))
                ->when($above !== null, fn ($x) => $x->where('estimated_value', '>=', $above))
                ->when(Str::contains($q, 'won'), fn ($x) => $x->where('status', 'won'))
                ->when(Str::contains($q, 'open'), fn ($x) => $x->open())
                ->latest()->limit(10)->get();

            $results['leads'] = $leads->map(fn ($l) => ['#'.$l->id.' '.$l->name, $l->status, (float) $l->estimated_value])->all();
            $results['parsed'][] = 'leads'.($division ? " in {$division}" : '').($above ? " above {$above}" : '');
        }

        if (Str::contains($q, ['invoice', 'invoices'])) {
            $invoices = Invoice::query()
                ->when($division, fn ($x) => $x->where('division', $division))
                ->when(Str::contains($q, 'overdue'), fn ($x) => $x->outstanding()->where('due_date', '<', now()))
                ->when(Str::contains($q, 'outstanding') && ! Str::contains($q, 'overdue'), fn ($x) => $x->outstanding())
                ->when(Str::contains($q, 'paid'), fn ($x) => $x->where('status', 'paid'))
                ->latest()->limit(10)->get();

            $results['invoices'] = $invoices->map(fn ($i) => [$i->reference, $i->customer?->name, (float) $i->balance_due])->all();
            $results['parsed'][] = Str::contains($q, 'overdue') ? 'overdue invoices' : 'invoices';
        }

        if (Str::contains($q, ['customer', 'customers', 'garage', 'distributor', 'dealer'])) {
            $type = collect(['garage', 'distributor', 'dealer', 'workshop', 'corporate'])->first(fn ($t) => Str::contains($q, $t));

            $customers = Customer::query()
                ->when($type, fn ($x) => $x->where('type', $type))
                ->when($division, fn ($x) => $x->where('division', $division))
                ->orderBy('name')->limit(10)->get();

            $results['customers'] = $customers->map(fn ($c) => [$c->displayName(), $c->type, (float) $c->outstanding_balance])->all();
            $results['parsed'][] = ($type ?? 'customers');
        }

        return $results;
    }
}