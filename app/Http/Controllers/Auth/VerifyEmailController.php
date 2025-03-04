<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;
use Inertia\Inertia;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request, $id, $hash)
    {
        try {
            $user = User::findOrFail($id);

            // Check if the hash matches
            if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
                \Log::error('Email verification failed for user ' . $user->id . '. Hash mismatch.');
                return redirect()->route('login')
                    ->with('error', 'Invalid verification link.');
            }

            // If user hasn't verified their email yet
            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
                event(new Verified($user));
                \Log::info('Email verified successfully for user ' . $user->id);
            }

            // If user is not logged in, log them in
            if (! Auth::check()) {
                Auth::login($user);
                \Log::info('User ' . $user->id . ' logged in during email verification');
            }

            \Log::info('Attempting to render verification success page', [
                'user_id' => $user->id,
                'component' => 'Auth/VerificationSuccess'
            ]);

            // Return a simpler response first for testing
            return redirect()->route('dashboard')
                ->with('success', 'Email verified successfully!')
                ->with('verified', true);

        } catch (\Exception $e) {
            \Log::error('Error in email verification: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $id ?? null
            ]);

            return redirect()->route('login')
                ->with('error', 'There was a problem verifying your email. Please try again.');
        }
    }
}
