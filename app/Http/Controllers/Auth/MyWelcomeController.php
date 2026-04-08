<?php

namespace App\Http\Controllers\Auth;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User;
use Illuminate\Validation\Rules\Password;
use Spatie\WelcomeNotification\WelcomeController as BaseWelcomeController;

class MyWelcomeController extends BaseWelcomeController
{
    public function showWelcomeForm(Request $request, User $user)
    {
        return view('auth.set-password', ['user' => $user]);
    }

    public function rules()
    {
        return [
            'password' => 'required|string|confirmed|min:8',
        ];
    }

    public function sendPasswordSavedResponse(): Response
    {
        return redirect()->route('dashboard');
    }

    public function savePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'string', Password::defaults()->mixedCase()->letters()->numbers(), 'confirmed'],
        ]);

        $user->password = bcrypt($request->password);
        $user->email_verified_at = now();
        $user->save();

        // Optionally log the user in
        auth()->login($user);

        return redirect()->route('dashboard')->with('status', 'Password set successfully!');
    }
}
