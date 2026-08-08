<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Services\Accounting\ZohoTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ZohoConnectController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $query = http_build_query([
            'scope' => 'ZohoBooks.fullaccess.all',
            'client_id' => config('zoho.client_id'),
            'response_type' => 'code',
            'redirect_uri' => config('zoho.redirect_uri'),
            'access_type' => 'offline',
            'prompt' => 'consent',
        ]);

        return redirect()->away(config('zoho.accounts_url').'/oauth/v2/auth?'.$query);
    }

    public function callback(Request $request, ZohoTokenService $tokens): RedirectResponse
    {
        $tokens->storeFromCode(
            (string) $request->query('code'),
            (string) config('zoho.organization_id'),
        );

        return redirect()->route('settings.zoho')->with('status', 'Zoho Books connected.');
    }
}