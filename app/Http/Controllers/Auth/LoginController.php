<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\Actions\Auth\LogoutUserAction;
use App\DTOs\Auth\LoginDTO;
use App\Enums\LoginStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request, AuthenticateUserAction $authenticate): RedirectResponse
    {
        $result = $authenticate->execute(LoginDTO::fromRequest($request));

        if ($result->status === LoginStatus::Success) {
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => $result->message])->onlyInput('email');
    }

    public function logout(Request $request, LogoutUserAction $logout): RedirectResponse
    {
        $logout->execute($request);

        return redirect()->route('login');
    }
}