<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            Log::info('Fetching user data for dashboard.', ['user_id' => $request->user()->id]);

            $user = User::select('users.*', 'roles.name as role')
                ->join('roles', 'users.role', '=', 'roles.id')
                ->where('users.id', $request->user()->id)
                ->first();

            if ($user) {
                Log::info('User data retrieved successfully for dashboard.', ['user_id' => $user->id, 'user_name' => $user->name, 'role' => $user->role]);
            } else {
                Log::warning('User data not found for dashboard.', ['user_id' => $request->user()->id]);
            }

            return Inertia::render('Dashboard', [
                'name' => $user->name,
                'role' => $user->role,
            ]);
        } catch (\Exception $e) {
            Log::error('Error occurred while fetching user for dashboard.', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id
            ]);
            return redirect()->back()->with('error', 'Unable to fetch user. Please try again.');
        }
    }
}
