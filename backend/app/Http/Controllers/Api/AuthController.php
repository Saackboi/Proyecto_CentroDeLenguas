<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'correo' => ['required', 'email'],
            'contrasena' => ['required', 'string'],
        ]);

        $token = auth('api')->attempt([
            'correo' => $credentials['correo'],
            'password' => $credentials['contrasena'],
        ]);

        if (!$token) {
            return response()->json(['message' => 'Credenciales invalidas'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Sesion cerrada']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    private function respondWithToken(string $token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}
