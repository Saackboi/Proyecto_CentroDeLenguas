<?php

namespace App\Services;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EstudiantePasswordService
{
    public function cambiar(Request $request)
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->role !== 'Estudiante') {
            return ApiResponse::forbidden('No autorizado.');
        }

        $validated = $request->validate([
            'contrasena_actual' => ['required', 'string'],
            'contrasena_nueva' => ['required', 'string', 'min:8'],
        ]);

        if (!Hash::check($validated['contrasena_actual'], $usuario->password)) {
            return ApiResponse::error('Contrasena actual incorrecta.', 400, null, 'invalid_password');
        }

        DB::table('users')
            ->where('id', $usuario->id)
            ->update([
                'password' => Hash::make($validated['contrasena_nueva']),
            ]);

        return ApiResponse::success(null, 'Contrasena actualizada.');
    }
}
