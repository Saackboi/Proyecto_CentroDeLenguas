<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthService
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'correo' => ['required_without:email', 'email'],
            'email' => ['required_without:correo', 'email'],
            'contrasena' => ['required_without:password', 'string'],
            'password' => ['required_without:contrasena', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Datos de inicio de sesion invalidos', 'errors' => $validator->errors()], 422);
        }

        $correo = (string) $request->input('correo', $request->input('email'));
        $contrasena = (string) $request->input('contrasena', $request->input('password'));

        $token = auth('api')->attempt([
            'email' => $correo,
            'password' => $contrasena,
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
