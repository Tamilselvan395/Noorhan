<?php

namespace App\Enums;

enum CustomerType: string
{
    case Retail       = 'retail';
    case Garage       = 'garage';
    case AutoPartsShop = 'auto_parts_shop';
    case Distributor  = 'distributor';
    case Dealer       = 'dealer';
    case Workshop     = 'workshop';
    case Corporate    = 'corporate';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}