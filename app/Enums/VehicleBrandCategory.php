<?php

namespace App\Enums;

enum VehicleBrandCategory: string
{
    case Japanese  = 'japanese';
    case American  = 'american';
    case European  = 'european';
    case Korean    = 'korean';
    case Chinese   = 'chinese';
    case Unknown   = 'unknown';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}