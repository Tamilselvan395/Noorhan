<?php

namespace App\Livewire\SalesOrders;

use App\Actions\SalesOrders\AdvanceSalesOrderStatusAction;
use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use RuntimeException;

class OrderShow extends Component
{
    public SalesOrder $order;

    public function mount(SalesOrder $order): void
    {
        $this->order = $order;
    }

    public function advance(string $status, AdvanceSalesOrderStatusAction $advance): void
    {
        Gate::authorize('update', $this->order);

        try {
            $advance->execute($this->order->fresh(), SalesOrderStatus::from($status));
            $this->dispatch('notify', message: 'Order updated.', type: 'success');
        } catch (RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function nextStatuses(AdvanceSalesOrderStatusAction $advance): array
    {
        return $advance->transitions()[$this->order->status->value] ?? [];
    }

    public function generateInvoice(\App\Actions\Invoices\CreateInvoiceFromOrderAction $create)
    {
        try {
            $invoice = $create->execute($this->order->fresh());
            $this->dispatch('notify', message: "Invoice {$invoice->reference} generated.", type: 'success');
            return redirect()->route('invoices.show', $invoice);
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function render(): View
    {
        return view('livewire.sales-orders.order-show', [
            'items' => $this->order->items()->with('product')->get(),
            'timeline' => $this->order->activities()->with('user')->latest()->get(),
        ]);
    }
}