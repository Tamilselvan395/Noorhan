<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SecurityEvent;
use App\Events\Auth\PasswordChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Repositories\SecurityLogRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function __construct(private SecurityLogRepository $security) {}

    public function showResetForm(ResetPasswordRequest $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset($request->validated(), function (User $user, string $password) {
            $user->forceFill([
                'password'              => $password,
                'password_changed_at'   => now(),
                'failed_login_attempts' => 0,
                'locked_until'          => null,
            ])->save();

            $this->security->log(SecurityEvent::PasswordResetCompleted, $user);
            event(new PasswordChanged($user));
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}