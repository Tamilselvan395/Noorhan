<?php

namespace App\Enums;

enum ProductUnit: string
{
    case Pcs = 'pcs';
    case Set = 'set';
    case Litre = 'litre';
    case Bottle = 'bottle';
    case Drum = 'drum';
    case Carton = 'carton';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}