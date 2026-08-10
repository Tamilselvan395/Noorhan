<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DivisionController extends Controller
{
    public function swiftec(): View
    {
        return view('divisions.swiftec');
    }

    public function wiperex(): View
    {
        return view('divisions.wiperex');
    }

    public function otozaar(): View
    {
        return view('divisions.otozaar');
    }
}