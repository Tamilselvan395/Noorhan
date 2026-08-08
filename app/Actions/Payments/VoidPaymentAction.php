<?php

namespace App\Actions\Payments;

use App\Enums\PaymentStatus;
use App\Events\Payments\PaymentVoided;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VoidPaymentAction
{
    public function execute(Payment $payment): void
    {
        if ($payment->status() === PaymentStatus::Voided) {
            throw new RuntimeException('Payment is already voided.');
        }

        DB::transaction(function () use ($payment) {
            $customer = $payment->customer;

            foreach ($payment->invoices as $invoice) {
                $allocated = (float) $invoice->pivot->allocated_amount;

                // Reverse invoice balances
                $invoice->decrement('paid_amount', $allocated);
                $invoice->updateBalances();

                // Reverse customer outstanding
                $customer->increment('outstanding_balance', $allocated);
            }

            // Reverse customer credit if there was an unallocated amount
            $unallocated = $payment->unallocatedAmount();
            if ($unallocated > 0) {
                $customer->decrement('credit_balance', $unallocated);
            }

            $payment->update(['status' => PaymentStatus::Voided->value]);
            $payment->logActivity('voided the payment and reversed all allocations');

            event(new PaymentVoided($payment));
        });
    }
}