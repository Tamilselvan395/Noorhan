<?php

namespace App\DTOs\Payments;

use App\DTOs\BaseDTO;

readonly class PaymentDTO extends BaseDTO
{
    public function __construct(
        public int $customer_id,
        public float $amount,
        public string $currency = 'USD',
        public string $payment_date = '',
        public string $method = 'cash',
        public ?string $reference_number = null,
        public ?string $notes = null,
        /** @var array<int, float> invoice_id => allocated_amount */
        public array $allocations = [], 
    ) {}
}
