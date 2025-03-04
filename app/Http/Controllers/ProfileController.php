<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        Log::info('User accessing profile edit page.', ['user_id' => $request->user()->id]);

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        Log::info('User updating profile.', ['user_id' => $user->id, 'email' => $user->email]);

        $user->fill($request->validated());

        // If the email was changed, reset email verification.
        if ($user->isDirty('email')) {
            Log::info('User changed email address, resetting email verification.', ['user_id' => $user->id, 'old_email' => $user->getOriginal('email'), 'new_email' => $user->email]);
            $user->email_verified_at = null;
        }

        $user->save();

        Log::info('User profile updated successfully.', ['user_id' => $user->id]);

        return Redirect::route('profile.edit')->with('status', 'Profile updated successfully!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        Log::warning('User account deletion request received.', ['user_id' => $user->id, 'email' => $user->email]);

        // Log the user out
        Auth::logout();

        // Delete the user account
        $user->delete();

        Log::info('User account deleted successfully.', ['user_id' => $user->id]);

        // Invalidate and regenerate session to complete the logout process
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('status', 'Your account has been deleted successfully.');
    }
}
