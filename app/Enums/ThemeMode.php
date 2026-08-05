<?php

namespace App\Enums;

enum ThemeMode: string
{
    case LIGHT = 'light';
    case DARK = 'dark';
    case SYSTEM = 'system';
}