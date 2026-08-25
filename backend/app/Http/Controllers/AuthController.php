<?php

namespace App\Http\Controllers;

use App\Actions\Auth\LoginUser;
use App\Actions\Auth\RegisterUser;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;
class AuthController extends Controller
{
    public function login(LoginRequest $request, LoginUser $loginUser)
    {

        $credentials = $request->validated();
        if ($loginUser->execute($credentials)) {
            $request->session()->regenerate();

        } else {
            return response()->json([
                'message' => "Usuario nao autenticado",
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'message' => 'Login realizado com sucesso',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'bio' => $user->bio,
                    'homeRiver' => $user->home_river,
                    'avatarUrl' => null,
                    'coverUrl' => null,
                ],
            ],
        ]);
    }

    public function logout(Request $request)
    {

        Auth::guard('web')->logout();
        $request->session()->invalidate();

        $request->session()->regenerateToken();
        return response()->json([
            'message' => 'Logout realizado com sucesso',
            'data' => null,
        ]);
    }

    public function register(
        RegisterRequest $request,
        RegisterUser $registerUser
    ) {
        $user = $registerUser->execute($request->validated());
        return response()->json([
            'message' => 'Cadastro realizado com sucesso',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'bio' => $user->bio,
                    'homeRiver' => $user->home_river,
                    'avatarUrl' => null,
                    'coverUrl' => null,
                ],
            ],
        ], 201);

    }
}
