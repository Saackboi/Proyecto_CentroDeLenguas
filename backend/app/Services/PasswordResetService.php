<?php

namespace App\Services;

use App\Mail\EstudianteResetPasswordMail;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetService
{
    public function solicitar(Request $request, string $tipoUsuario, string $portal, string $rutaReset)
    {
        $validated = $request->validate([
            'correo' => ['required', 'email', 'max:100'],
        ]);

        $correo = strtolower(trim($validated['correo']));

        $usuario = DB::table('users')
            ->where('email', $correo)
            ->where('role', $tipoUsuario)
            ->first();

        if (!$usuario) {
            return ApiResponse::success(null, 'Si el correo existe, recibiras instrucciones de recuperacion.');
        }

        $token = Str::random(64);
        $expira = now()->addDay();

        DB::table('users')
            ->where('email', $correo)
            ->update([
                'token_recuperacion' => $token,
                'expiracion_token' => $expira,
            ]);

        $link = rtrim(config('app.url'), '/') . $rutaReset . '?token=' . $token;

        Mail::to($correo)->send(new EstudianteResetPasswordMail($correo, $link, strtolower($tipoUsuario), $portal));

        return ApiResponse::success(null, 'Si el correo existe, recibiras instrucciones de recuperacion.');
    }

    public function resetear(Request $request, string $tipoUsuario)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'contrasena' => ['required', 'string', 'min:8'],
        ]);

        $usuario = DB::table('users')
            ->where('token_recuperacion', $validated['token'])
            ->where('role', $tipoUsuario)
            ->first();

        if (!$usuario) {
            return ApiResponse::error('Token invalido.', 400, null, 'invalid_token');
        }

        if ($usuario->expiracion_token && now()->greaterThan($usuario->expiracion_token)) {
            return ApiResponse::error('Token expirado.', 400, null, 'token_expired');
        }

        DB::table('users')
            ->where('email', $usuario->email)
            ->update([
                'password' => Hash::make($validated['contrasena']),
                'token_recuperacion' => null,
                'expiracion_token' => null,
            ]);

        return ApiResponse::success(null, 'Contrasena actualizada.');
    }
}
