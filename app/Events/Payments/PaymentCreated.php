<?php
namespace App\Events\Payments;
use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
class PaymentCreated { use Dispatchable; public function __construct(public Payment $payment) {} }