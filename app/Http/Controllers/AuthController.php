<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Credențialele furnizate nu sunt corecte.',
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nume_firma' => 'required|string|max:255',
            'strada' => 'required|string|max:255',
            'numar_strada' => 'required|string|max:10',
            'cui' => 'required|string|max:20|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'account_type' => 'required|in:client,furnizor'
        ]);

        $user = User::create([
            'nume_firma' => $validated['nume_firma'],
            'strada' => $validated['strada'],
            'numar_strada' => $validated['numar_strada'],
            'cui' => $validated['cui'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'account_type' => $validated['account_type'],
            'connect_id' => uniqid()
        ]);

        Auth::login($user);

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
} 