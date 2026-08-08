<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Cheque = 'cheque';
    case CreditCard = 'credit_card';
    case Online = 'online';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}