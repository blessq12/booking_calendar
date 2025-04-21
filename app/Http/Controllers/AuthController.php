<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function authorize(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $errors = [];

        if (empty($credentials['email'])) {
            $errors['email'] = 'Email is required';
        }

        if (empty($credentials['password'])) {
            $errors['password'] = 'Password is required';
        }

        if (\App\Models\User::where('email', $credentials['email'])->count() === 0) {
            $errors['email'] = 'Пользователь не найден';
        }

        if (!Auth::attempt($credentials)) {
            $errors['password'] = 'Неверный пароль';
            return redirect()->back()->withErrors($errors);
        }

        return redirect()->intended('booking');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
