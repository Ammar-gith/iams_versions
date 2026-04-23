<?php

// app/Http/Controllers/PasswordResetRequestController.php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PasswordResetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class PasswordResetRequestController extends Controller
{

    // User requests form
    public function create()
    {
        return view('auth.password_reset_request');
    }

    // User requests reset
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Prevent duplicate unresolved requests
        if (PasswordResetRequest::where('user_id', $user->id)->where('resolved', false)->exists()) {
            return back()->with('error', 'You already have a pending reset request.');
        }

        // generate a reset token (Laravel built-in)
        $token = Password::broker()->createToken($user);

        // dd($token);

        // store in your custom password_reset_requests table
        PasswordResetRequest::create([
            'user_id'   => $user->id,
            'token'     => $token,
            'resolved'  => false,
        ]);

        // also store in Laravel’s default password_reset_tokens table (needed by Breeze reset-password UI)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token'      => $token,
                'created_at' => now(),
            ]
        );

        return redirect()->route('login')->with('success', 'Password reset request submitted. The administrator will reset and email your new password.');
    }

    // Super Admin resets password
    public function resetPassword($id)
    {
        $request = PasswordResetRequest::findOrFail($id);
        $user = $request->user;

        // Generate a random password
        $newPassword = str()->random(10);

        // Update password
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Mark request resolved
        $request->update(['resolved' => true]);

        // Email new password
        Mail::raw("Hello {$user->name},

        Your account credentials have been reset:

        Username: {$user->email}
        Password: {$newPassword}

        Please log in and change your password immediately.", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your New Login Credentials');
        });

        return back()->with('success', 'Password reset and emailed to user.');
    }
}
