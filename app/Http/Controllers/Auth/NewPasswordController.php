<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\PasswordResetRequest; // Custom model

class NewPasswordController extends Controller
{
    // Display the password reset view
    public function create(Request $request, string $token): View
    {
        // First, check your custom table
        $resetRequest = PasswordResetRequest::where('token', $token)->first();

        if (!$resetRequest) {
            abort(404, 'Invalid or expired reset token.');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $resetRequest->user->email, // from relation
        ]);

        // return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        // Check token in your custom table
        $resetRequest = PasswordResetRequest::with('user')
            ->where('token', $request->token)
            ->first();

        if (! $resetRequest || $resetRequest->user->email !== $request->email) {
            return back()->withErrors(['email' => 'This password reset token is invalid.']);
        }

        // Update password
        $user = $resetRequest->user;
        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        // Fire event (for Breeze login/session clearing, etc.)
        event(new PasswordReset($user));

        // Delete the used reset request
        $resetRequest->delete();

        return redirect()->route('login')->with([
            'status' => 'Password updated successfully!',
            'danger' => 'Something went wrong while changing passwrod!'
        ]);

        // $request->validate([
        //     'token' => ['required'],
        //     'email' => ['required', 'email'],
        //     'password' => ['required', 'confirmed', Rules\Password::defaults()],
        // ]);


        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        // $status = Password::reset(
        //     $request->only('email', 'password', 'password_confirmation', 'token'),
        //     function ($user) use ($request) {
        //         $user->forceFill([
        //             'password' => Hash::make($request->password),
        //             'remember_token' => Str::random(60),
        //         ])->save();

        //         // Event to cleanup the requests
        //         event(new PasswordReset($user));
        //         PasswordResetRequest::where('token', $request->token)->delete();
        //     }
        // );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        // return $status == Password::PASSWORD_RESET
        //             ? redirect()->route('login')->with('status', __($status))
        //             : back()->withInput($request->only('email'))
        //                     ->withErrors(['email' => __($status)]);
    }
}
