<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnsubscribeController extends Controller
{
    public function handle(Request $request, Customer $customer, string $token): View
    {
        abort_unless(
            hash_equals(sha1($customer->id.$customer->email.config('app.key')), $token),
            403,
        );

        $customer->update(['email_opted_out' => true]);
        $customer->logActivity('unsubscribed from marketing emails');

        return view('unsubscribe', ['customer' => $customer]);
    }
}