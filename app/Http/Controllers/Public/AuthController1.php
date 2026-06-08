<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController1 extends Controller
{


public function login(Request $request)
{
    if (Auth::attempt($request->only('email', 'password'))) {

        $user = Auth::user();

        if ($user->role === 'admin') {
            $redirect = route('admin.dashboard');
        } elseif ($user->role === 'artisan') {
            $redirect = route('artisan.dashboard');
        } else {
            $redirect = route('client.dashboard');
        }

        return response()->json([
            'success' => true,
            'redirect' => $redirect
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Email ou mot de passe incorrect'
    ], 401);
}
}