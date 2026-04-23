<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminPasswordResetController extends Controller
{
    public function edit($id)
    {
        $request = PasswordResetRequest::with('user')->findOrFail($id);
        return view('admin.password_resets.edit', compact('request'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $resetRequest = PasswordResetRequest::with('user')->findOrFail($id);
        $user = $resetRequest->user;

        // Use Breeze’s password hashing logic (same as in NewPasswordController)
        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        $resetRequest->update(['status' => 'approved']);

        return redirect()->route('admin.password_resets.index')
            ->with('success', "Password reset successfully for {$user->email}");
    }
}
