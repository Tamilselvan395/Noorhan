<?php

namespace App\Http\Controllers;

use App\Actions\Quotations\AcceptQuotationAction;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class QuotationPublicController extends Controller
{
    /** Signed, no-auth customer view (also the print/PDF layout). */
    public function show(Request $request, Quotation $quotation): View
    {
        abort_unless($request->hasValidSignature(), 403);

        return view('quotations.public', ['quotation' => $quotation->load('items', 'customer')]);
    }

    public function accept(Request $request, Quotation $quotation, AcceptQuotationAction $accept)
    {
        abort_unless($request->hasValidSignature(), 403);

        try {
            $accept->execute($quotation);
        } catch (RuntimeException $e) {
            return back()->with('public_error', $e->getMessage());
        }

        return back()->with('public_status', 'Quotation accepted — thank you!');
    }
}