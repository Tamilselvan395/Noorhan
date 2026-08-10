<?php

namespace App\Http\Controllers\Portal;

use App\Actions\Quotations\AcceptQuotationAction;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    private function customer(): Customer
    {
        return auth()->user()->customer;
    }

    /** Strict scoping: customers only ever see their own documents. */
    private function owned(string $class, int $id)
    {
        $record = $class::query()
            ->where('customer_id', $this->customer()->id)
            ->findOrFail($id);

        return $record;
    }

    public function dashboard(): View
    {
        $customer = $this->customer();

        return view('portal.dashboard', [
            'customer' => $customer,
            'outstanding' => (float) Invoice::where('customer_id', $customer->id)->outstanding()->sum('balance_due'),
            'openOrders' => SalesOrder::where('customer_id', $customer->id)->open()->count(),
            'openQuotations' => Quotation::where('customer_id', $customer->id)->where('status', 'sent')->count(),
            'recentInvoices' => Invoice::where('customer_id', $customer->id)->latest()->limit(5)->get(),
            'recentOrders' => SalesOrder::where('customer_id', $customer->id)->latest()->limit(5)->get(),
        ]);
    }

    public function quotations(): View
    {
        return view('portal.quotations', [
            'quotations' => Quotation::where('customer_id', $this->customer()->id)->latest()->get(),
        ]);
    }

    public function quotationShow(int $quotation): View
    {
        return view('portal.quotation-show', [
            'quotation' => $this->owned(Quotation::class, $quotation)->load('items'),
        ]);
    }

    public function quotationAccept(Request $request, int $quotation, AcceptQuotationAction $accept)
    {
        $record = $this->owned(Quotation::class, $quotation);

        $accept->execute($record);

        return back()->with('status', 'Quotation accepted — thank you!');
    }

    public function quotationDecline(Request $request, int $quotation)
    {
        $record = $this->owned(Quotation::class, $quotation);

        abort_unless($record->status === 'sent', 422);

        $record->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_reason' => 'Declined by customer via portal.',
        ]);

        $record->logActivity('was declined by the customer via portal');

        return back()->with('status', 'Quotation declined.');
    }

    public function invoices(): View
    {
        return view('portal.invoices', [
            'invoices' => Invoice::where('customer_id', $this->customer()->id)->latest()->get(),
        ]);
    }

    public function invoiceShow(int $invoice): View
    {
        return view('portal.invoice-show', [
            'invoice' => $this->owned(Invoice::class, $invoice)->load('items'),
        ]);
    }

    public function orders(): View
    {
        return view('portal.orders', [
            'orders' => SalesOrder::where('customer_id', $this->customer()->id)->latest()->get(),
        ]);
    }

    public function orderShow(int $order): View
    {
        return view('portal.order-show', [
            'order' => $this->owned(SalesOrder::class, $order)->load('items'),
        ]);
    }

    public function payments(): View
    {
        return view('portal.payments', [
            'payments' => \App\Models\Payment::where('customer_id', $this->customer()->id)
                ->where('status', 'completed')->latest('payment_date')->get(),
        ]);
    }

    public function profile(): View
    {
        return view('portal.profile');
    }

    public function appointments(): View
    {
        return view('portal.appointments', [
            'appointments' => \App\Models\Appointment::with('service')
                ->where('customer_id', $this->customer()->id)
                ->orderByDesc('scheduled_at')->get(),
        ]);
    }

    public function appointmentCancel(int $appointment)
    {
        $record = $this->owned(\App\Models\Appointment::class, $appointment);

        abort_unless(auth()->user()->can('update', $record), 403);

        app(\App\Actions\Otozaar\AdvanceAppointmentAction::class)
            ->execute($record, \App\Enums\AppointmentStatus::Cancelled, auth()->user());

        return back()->with('status', 'Appointment cancelled.');
    }
}