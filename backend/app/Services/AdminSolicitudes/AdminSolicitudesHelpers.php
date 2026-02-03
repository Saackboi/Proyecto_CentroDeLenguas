<?php

// Helpers compartidos para servicios de solicitudes administrativas

namespace App\Services\AdminSolicitudes;

use App\Support\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait AdminSolicitudesHelpers
{
    private function asegurarAdmin()
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->role !== 'Admin') {
            return ApiResponse::forbidden('No autorizado.');
        }

        return null;
    }

    private function crearNotificacion(string $idEstudiante, string $titulo, string $cuerpo, string $tipo = 'estado'): void
    {
        DB::table('notifications')->insert([
            'student_id' => $idEstudiante,
            'title' => $titulo,
            'body' => $cuerpo,
            'type' => $tipo,
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function crearCuentaEstudiante(string $correo): ?string
    {
        $correo = strtolower(trim($correo));

        if ($correo === '') {
            return null;
        }

        $existe = DB::table('users')
            ->where('email', $correo)
            ->exists();

        if ($existe) {
            return null;
        }

        $passwordPlano = Str::random(10);

        DB::table('users')->insert([
            'email' => $correo,
            'password' => Hash::make($passwordPlano),
            'role' => 'Estudiante',
            'token_recuperacion' => null,
            'expiracion_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $passwordPlano;
    }
}
