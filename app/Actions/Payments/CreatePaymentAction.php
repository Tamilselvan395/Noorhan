<?php

namespace App\Actions\Payments;

use App\DTOs\Payments\PaymentDTO;
use App\Events\Payments\PaymentCreated;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreatePaymentAction
{
    public function execute(PaymentDTO $dto, ?User $creator): Payment
    {
        $customer = Customer::findOrFail($dto->customer_id);
        $totalAllocated = array_sum($dto->allocations);

        if ($totalAllocated > $dto->amount) {
            throw new RuntimeException('Allocated amount cannot exceed the total payment received.');
        }

        return DB::transaction(function () use ($dto, $creator, $customer, $totalAllocated) {
            $payment = new Payment([
                'customer_id' => $dto->customer_id,
                'amount' => $dto->amount,
                'currency' => $dto->currency,
                'payment_date' => $dto->payment_date ?: now()->toDateString(),
                'method' => $dto->method,
                'reference_number' => $dto->reference_number,
                'status' => 'completed',
                'notes' => $dto->notes,
                'created_by' => $creator?->id,
            ]);
            $payment->save();
            $payment->update(['reference' => 'PAY-'.str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT)]);

            // 1. Apply allocations to invoices
            foreach ($dto->allocations as $invoiceId => $allocated) {
                if ($allocated <= 0) continue;

                $invoice = Invoice::findOrFail($invoiceId);
                
                // Prevent over-allocating to a single invoice
                if ($allocated > (float) $invoice->balance_due) {
                    throw new RuntimeException("Allocation exceeds balance for invoice {$invoice->reference}.");
                }

                $invoice->increment('paid_amount', $allocated);
                $invoice->updateBalances(); // Handles status transition (partial/paid)

                $payment->invoices()->attach($invoiceId, ['allocated_amount' => $allocated]);
            }

            // 2. Update Customer Balances
            // Outstanding decreases by the amount actually applied to invoices
            if ($totalAllocated > 0) {
                $customer->decrement('outstanding_balance', $totalAllocated);
            }

            // If they overpaid, the remainder becomes credit
            $unallocated = $dto->amount - $totalAllocated;
            if ($unallocated > 0) {
                $customer->increment('credit_balance', $unallocated);
            }

            $payment->logActivity("recorded payment of {$dto->amount} {$dto->currency}");

            event(new PaymentCreated($payment));

            return $payment->fresh();
        });
    }
}