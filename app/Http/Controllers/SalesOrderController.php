<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', SalesOrder::class);

        return view('sales-orders.index');
    }

    public function show(SalesOrder $order): View
    {
        Gate::authorize('view', $order);

        return view('sales-orders.show', ['order' => $order]);
    }
}