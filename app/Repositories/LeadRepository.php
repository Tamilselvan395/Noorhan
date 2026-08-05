<?php

namespace App\Repositories;

use App\Enums\Division;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Support\Collection;

class LeadRepository extends BaseRepository
{
    protected function model(): string
    {
        return Lead::class;
    }

    public function forKanban(?Division $division = null, int $limit = 30): Collection
    {
        return collect(LeadStatus::cases())->mapWithKeys(fn (LeadStatus $status) => [
            $status->value => Lead::query()
                ->status($status)
                ->when($division, fn ($q) => $q->division($division))
                ->latest()
                ->limit($limit)
                ->get(),
        ]);
    }
}