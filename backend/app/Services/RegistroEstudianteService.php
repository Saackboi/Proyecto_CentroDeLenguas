<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegistroEstudianteService
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_estudiante' => ['required', 'string', 'max:30'],
            'correo' => ['required', 'email', 'max:100'],
            'contrasena' => ['required', 'string', 'min:8'],
        ]);

        $idEstudiante = trim($validated['id_estudiante']);
        $correo = strtolower(trim($validated['correo']));

        $estudiante = DB::table('students as s')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->select('s.status', 'p.email_personal', 'p.email_institucional')
            ->where('s.id', $idEstudiante)
            ->first();

        if (!$estudiante) {
            return response()->json([
                'message' => 'No existe un registro de estudiante con esa identificación.',
            ], 404);
        }

        if ($estudiante->status === 'Inactivo') {
            return response()->json([
                'message' => 'El estudiante está inactivo y no puede crear una cuenta.',
            ], 409);
        }

        $correosValidos = array_filter([
            strtolower($estudiante->email_personal ?? ''),
            strtolower($estudiante->email_institucional ?? ''),
        ]);

        if (!in_array($correo, $correosValidos, true)) {
            return response()->json([
                'message' => 'El correo no coincide con el registro del estudiante.',
            ], 409);
        }

        $existeUsuario = DB::table('users')->where('email', $correo)->exists();
        if ($existeUsuario) {
            return response()->json([
                'message' => 'Ya existe una cuenta con ese correo.',
            ], 409);
        }

        DB::table('users')->insert([
            'email' => $correo,
            'password' => Hash::make($validated['contrasena']),
            'role' => 'Estudiante',
            'token_recuperacion' => null,
            'expiracion_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Cuenta creada correctamente. Ya puede iniciar sesion.',
        ], 201);
    }
}
