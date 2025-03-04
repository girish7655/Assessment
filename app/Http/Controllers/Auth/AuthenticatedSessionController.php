<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Log as LogModel;  // Import your custom Log model
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;  // This is for file-based logging
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        // Log when the login page is displayed
        Log::info('Displaying login page');  // File-based log

        // Insert log into the database log table using LogModel
        LogModel::create([
            'action' => 'Display Login Page',
            'description' => 'User accessed the login page.',
            'user_id' => null,  // No user since they are not logged in yet
        ]);

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            // Validate the login request
            $credentials = $request->validate([
                'email' => ['required','email'],
                'password' => ['required'],
            ]);

            // Attempt to login with the provided credentials
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();

                $user = User::select('users.*', 'roles.name as role')
                    ->join('roles', 'users.role', '=', 'roles.id')
                    ->where('users.id', $request->user()->id)
                    ->first();

                // Log the successful login attempt in file
                Log::info('User login successful', ['email' => $request->email, 'user_id' => $user->id, 'role' => $user->role]);

                // Insert log into the database log table using LogModel
                LogModel::create([
                    'user_id' => $user->id,
                    'action' => 'User Login',
                    'description' => 'User successfully logged in.',
                ]);

                // Redirect based on user role
                if ($user->role === 'customer' || $user->role === 'librarian') {
                    return redirect()->route('dashboard');
                } else {
                    // Log the failed role-based validation in file
                    Log::warning('User role mismatch', ['email' => $request->email, 'user_role' => $user->role]);

                    // Insert into database log
                    LogModel::create([
                        'user_id' => $user->id,
                        'action' => 'Role Mismatch',
                        'description' => 'User role did not match expected roles.',
                    ]);

                    return back()->withErrors(['email' => 'User doesn’t exist with this role']);
                }
            }

            // Log failed login attempt in file
            Log::warning('User login failed - incorrect credentials', ['email' => $request->email]);

            // Insert failed login attempt into database log table
            LogModel::create([
                'user_id' => null,  // No user since login failed
                'action' => 'Failed Login Attempt',
                'description' => 'Incorrect credentials provided.',
            ]);

            return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
        } catch (\Exception $e) {
            // Log the error details in file
            Log::error('User login failed due to an exception', [
                'error_message' => $e->getMessage(),
                'error_stack' => $e->getTraceAsString(),
                'email' => $request->email,
            ]);

            // Insert error log into database log table
            LogModel::create([
                'user_id' => null,  // No user since exception occurred before login
                'action' => 'Exception During Login',
                'description' => 'An error occurred during login.',
            ]);

            return redirect()->route('login')->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            // Log the logout action in file
            Log::info('User logout successful', ['user_id' => Auth::id(), 'email' => Auth::user()->email]);

            // Insert logout action into the database log table using LogModel
            LogModel::create([
                'user_id' => Auth::id(),
                'action' => 'User Logout',
                'description' => 'User logged out of the system.',
            ]);

            // Log out the user and invalidate the session
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('success', 'You have logged out successfully.');
        } catch (\Exception $e) {
            // Log the error details in file
            Log::error('User logout failed due to an exception', [
                'error_message' => $e->getMessage(),
                'error_stack' => $e->getTraceAsString(),
            ]);

            // Insert error log into database log table
            LogModel::create([
                'user_id' => Auth::id(),
                'action' => 'Exception During Logout',
                'description' => 'An error occurred during logout.',
            ]);

            return redirect()->route('login')->with('error', 'Something went wrong. Please try again.');
        }
    }
}
