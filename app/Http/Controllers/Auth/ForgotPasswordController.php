<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SecurityEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Repositories\SecurityLogRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private SecurityLogRepository $security,
        private UserRepository $users,
    ) {}

    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(ForgotPasswordRequest $request): RedirectResponse
    {
        $status = Password::sendResetLink($request->validated());

        if ($status === Password::RESET_LINK_SENT) {
            $user = $this->users->findByEmail($request->validated('email'));
            $this->security->log(SecurityEvent::PasswordResetRequested, $user);

            return back()->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }
}