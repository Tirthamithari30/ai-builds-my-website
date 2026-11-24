<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required' => 'Please enter your full name.',
            'email.required' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered. Please log in.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', "Welcome to AI Builds My Website, {$user->name}! Let's create your first business website.");
    }
}
