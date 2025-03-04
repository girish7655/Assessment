<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        Log::info('Password reset link request view is being displayed.');

        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        Log::info('Password reset link request received.', ['email' => $request->email]);

        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'We cannot find a user with that email address.'
        ]);

        // Attempt to send the password reset link to the user
        Log::info('Attempting to send password reset link to user', ['email' => $request->email]);

        $status = Password::sendResetLink(
            $request->only('email')
        );
        
        if ($status == Password::RESET_LINK_SENT) {
            Log::info('Password reset link sent successfully to: ' . $request->email);
            return back()->with('status', __($status));
        }

        // Log the failure if reset link could not be sent
        Log::warning('Failed to send password reset link', ['email' => $request->email, 'status' => $status]);

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
