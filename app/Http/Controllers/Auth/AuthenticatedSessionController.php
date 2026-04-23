<?php

namespace App\Http\Controllers\Auth;

use Log;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Providers\RouteServiceProvider;
use App\Http\Requests\Auth\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    // Display the login view
    public function create(): View
    {
        return view('auth.login');
    }

    // Handle an incoming authentication request
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        try {
            activity()->withRequestMetadata()->causedBy(auth()->user())->log('Logged in');
        } catch (\Exception $e) {
            Log::error('Activity log failed: ' . $e->getMessage());
        }


        return redirect()->intended(RouteServiceProvider::HOME)->with('status', 'Welcome back, ' . auth()->user()->name . '!');
    }

    // Destroy an authenticated session
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();                     // <-- capture before logout

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
        try {
            activity()->withRequestMetadata()->causedBy($user)->log('Logged out');
        } catch (\Exception $e) {
            Log::error('Activity log failed: ' . $e->getMessage());
        }

        return redirect('/')->with('status', 'You have been logged out.');
    }
}
