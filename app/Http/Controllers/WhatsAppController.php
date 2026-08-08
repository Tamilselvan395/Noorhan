<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class WhatsAppController extends Controller
{
    public function index(): View
    {
        return view('whatsapp.index');
    }
}