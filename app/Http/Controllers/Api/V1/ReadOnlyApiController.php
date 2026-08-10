<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommercialDocumentResource;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SupplierResource;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ReadOnlyApiController extends Controller
{
    public function products(Request $request)
    {
        return ProductResource::collection(
            Product::query()->with('category')
                ->when($request->query('division'), fn ($q) => $q->where('division', $request->query('division')))
                ->search($request->query('search'))
                ->paginate(min((int) $request->query('per_page', 25), 100)),
        );
    }

    public function suppliers(Request $request)
    {
        return SupplierResource::collection(
            Supplier::query()
                ->when($request->query('division'), fn ($q) => $q->where('division', $request->query('division')))
                ->search($request->query('search'))
                ->paginate(min((int) $request->query('per_page', 25), 100)),
        );
    }

    public function quotations(Request $request)
    {
        return CommercialDocumentResource::collection(
            Quotation::query()->with('items', 'customer')->latest()->paginate(min((int) $request->query('per_page', 25), 100)),
        );
    }

    public function quotation(Quotation $quotation)
    {
        return new CommercialDocumentResource($quotation->load('items', 'customer'));
    }

    public function salesOrders(Request $request)
    {
        return CommercialDocumentResource::collection(
            SalesOrder::query()->with('items', 'customer')->latest()->paginate(min((int) $request->query('per_page', 25), 100)),
        );
    }

    public function salesOrder(SalesOrder $order)
    {
        return new CommercialDocumentResource($order->load('items', 'customer'));
    }

    public function invoices(Request $request)
    {
        return CommercialDocumentResource::collection(
            Invoice::query()->with('items', 'customer')
                ->when($request->query('status'), fn ($q) => $q->where('status', $request->query('status')))
                ->latest()->paginate(min((int) $request->query('per_page', 25), 100)),
        );
    }

    public function invoice(Invoice $invoice)
    {
        return new CommercialDocumentResource($invoice->load('items', 'customer'));
    }

    public function payments(Request $request)
    {
        return PaymentResource::collection(
            Payment::query()->with('customer')->latest()->paginate(min((int) $request->query('per_page', 25), 100)),
        );
    }

    public function stats()
    {
        return response()->json(['data' => [
            'leads' => ['open' => Lead::open()->count(), 'total' => Lead::count()],
            'customers' => ['active' => Customer::active()->count(), 'total' => Customer::count()],
            'pipeline_value' => (float) Lead::open()->sum('estimated_value'),
            'orders_open' => SalesOrder::open()->count(),
            'invoices_outstanding' => (float) Invoice::outstanding()->sum('balance_due'),
            'collected_this_month' => (float) Payment::where('status', 'completed')->whereMonth('payment_date', now()->month)->sum('amount'),
        ]]);
    }
}