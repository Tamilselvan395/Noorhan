<?php

namespace App\Services\Reports;

use App\Contracts\ReportInterface;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Support\Carbon;

class EmployeesReport implements ReportInterface
{
    public function key(): string { return 'employees'; }
    public function label(): string { return 'Employees'; }
    public function group(): string { return 'Operations'; }

    public function columns(): array
    {
        return ['Employee', 'Leads Assigned', 'Leads Won', 'Quotations', 'Orders', 'Payments Recorded'];
    }

    public function rows(Carbon $from, Carbon $to): array
    {
        return User::query()->get()->map(fn (User $u) => [
            $u->name,
            Lead::query()->where('assigned_to', $u->id)->whereBetween('created_at', [$from, $to])->count(),
            Lead::query()->where('assigned_to', $u->id)->where('status', 'won')->whereBetween('created_at', [$from, $to])->count(),
            Quotation::query()->where('created_by', $u->id)->whereBetween('created_at', [$from, $to])->count(),
            SalesOrder::query()->where('created_by', $u->id)->whereBetween('created_at', [$from, $to])->count(),
            Payment::query()->where('created_by', $u->id)->whereBetween('created_at', [$from, $to])->count(),
        ])->all();
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        return ['Employees' => number_format(User::count())];
    }
}
