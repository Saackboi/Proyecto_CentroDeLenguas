<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Mail\EstudianteResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EstudiantePasswordResetController extends Controller
{
    public function solicitar(Request $request)
    {
        $validated = $request->validate([
            'correo' => ['required', 'email', 'max:100'],
        ]);

        $correo = strtolower(trim($validated['correo']));

        $usuario = DB::table('usuarios')
            ->where('correo', $correo)
            ->where('tipo_usuario', 'Estudiante')
            ->first();

        if (!$usuario) {
            return response()->json([
                'message' => 'Si el correo existe, recibiras instrucciones de recuperacion.',
            ]);
        }

        $token = Str::random(64);
        $expira = now()->addDay();

        DB::table('usuarios')
            ->where('correo', $correo)
            ->update([
                'token_recuperacion' => $token,
                'expiracion_token' => $expira,
            ]);

        $link = rtrim(config('app.url'), '/') . '/estudiantes/reset?token=' . $token;

        Mail::to($correo)->send(new EstudianteResetPasswordMail($correo, $link));

        return response()->json([
            'message' => 'Si el correo existe, recibiras instrucciones de recuperacion.',
        ]);
    }

    public function resetear(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'contrasena' => ['required', 'string', 'min:8'],
        ]);

        $usuario = DB::table('usuarios')
            ->where('token_recuperacion', $validated['token'])
            ->where('tipo_usuario', 'Estudiante')
            ->first();

        if (!$usuario) {
            return response()->json(['message' => 'Token invalido.'], 400);
        }

        if ($usuario->expiracion_token && now()->greaterThan($usuario->expiracion_token)) {
            return response()->json(['message' => 'Token expirado.'], 400);
        }

        DB::table('usuarios')
            ->where('correo', $usuario->correo)
            ->update([
                'contrasena' => Hash::make($validated['contrasena']),
                'token_recuperacion' => null,
                'expiracion_token' => null,
            ]);

        return response()->json(['message' => 'Contrasena actualizada.']);
    }
}
