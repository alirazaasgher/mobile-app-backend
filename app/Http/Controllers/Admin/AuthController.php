<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Show login form
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    // Handle login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt login using 'admins' guard if you have separate admin users
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'is_admin' => 1  // make sure only admin users can login
        ])) {
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials or not an admin.',
        ])->withInput();
    }

    // Logout
    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.login');
    }
}
