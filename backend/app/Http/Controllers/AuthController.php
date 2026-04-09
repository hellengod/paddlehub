<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials) === false) {
            return response()->json([
                'message' => "Usuario nao autenticado",
            ], 401);
        }
        $user = Auth::user();
        $token = $user->createToken('token');
        return response()->json([
            'message' => "Login realizado com sucesso",
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ]);
    }
}
