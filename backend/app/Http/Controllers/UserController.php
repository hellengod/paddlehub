<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Usuário autenticado recuperado com sucesso',
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
}
