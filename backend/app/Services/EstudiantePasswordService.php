<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EstudiantePasswordService
{
    public function cambiar(Request $request)
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->tipo_usuario !== 'Estudiante') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $validated = $request->validate([
            'contrasena_actual' => ['required', 'string'],
            'contrasena_nueva' => ['required', 'string', 'min:8'],
        ]);

        if (!Hash::check($validated['contrasena_actual'], $usuario->contrasena)) {
            return response()->json(['message' => 'Contrasena actual incorrecta.'], 400);
        }

        DB::table('usuarios')
            ->where('correo', $usuario->correo)
            ->update([
                'contrasena' => Hash::make($validated['contrasena_nueva']),
            ]);

        return response()->json(['message' => 'Contrasena actualizada.']);
    }
}
