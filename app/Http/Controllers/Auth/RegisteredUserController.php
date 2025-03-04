<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Log as LogModel;
use App\Mail\WelcomeEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        // Log the display of the registration view (file-based logging)
        Log::info('Displaying registration view');
        
        // Insert a log into the database log table
        LogModel::create([
            'action' => 'Display Registration View',
            'description' => 'User accessed the registration page.',
            'user_id' => null,  // No user logged in at this stage
            'ip_address' => request()->ip(),
        ]);

        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Log incoming registration attempt with email (file-based logging)
            Log::info('User registration attempt', ['email' => $request->email]);
            
            // Insert log into the database log table
            LogModel::create([
                'action' => 'User Registration Attempt',
                'description' => 'A registration attempt was made.',
                'user_id' => null,
                
            ]);

            // Check if the email already exists
            if (User::where('email', $request->email)->exists()) {
                Log::warning('Registration failed due to email already being in use', ['email' => $request->email]);

                LogModel::create([
                    'action' => 'Registration Failed',
                    'description' => 'Email already in use.',
                    'user_id' => null,
                    
                ]);

                return redirect()->route('register')
                    ->withErrors(['email' => 'The email address is already in use.'])
                    ->withInput();
            }

            // Validate request data
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'role' => 'required|in:1,2',
            ]);

            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // Generate verification URL
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $user->id,
                    'hash' => sha1($user->getEmailForVerification())
                ]
            );

            // Send welcome email with verification link
            try {
                Mail::to($user->email)->send(new WelcomeEmail($user, $verificationUrl));
                
                Log::info('Welcome email sent successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                
                LogModel::create([
                    'action' => 'Welcome Email Sent',
                    'description' => 'Welcome email sent successfully',
                    'user_id' => $user->id,
                    
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send welcome email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
                
                LogModel::create([
                    'action' => 'Email Send Failed',
                    'description' => 'Failed to send welcome email: ' . $e->getMessage(),
                    'user_id' => $user->id,
                    
                ]);
            }

            // Log in the user immediately after registration
            Auth::login($user);

            // Redirect to email verification notice
            return redirect()->route('verification.notice')
                ->with('success', 'Registration successful! Please check your email for verification link.');
        
        } catch (\Exception $e) {
            Log::error('User registration failed', [
                'error_message' => $e->getMessage(),
                'error_stack' => $e->getTraceAsString(),
                'email' => $request->email,
            ]);

            LogModel::create([
                'action' => 'Registration Failed',
                'description' => 'An error occurred during registration.',
                'user_id' => null,
                
            ]);

            return redirect()->route('register')
                ->with('error', 'Something went wrong. Please try again.');
        }
    }
}
