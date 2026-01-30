<?php

namespace App\Services;

use App\Support\ApiResponse;
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
            return ApiResponse::notFound('No existe un registro de estudiante con esa identificación.');
        }

        if ($estudiante->status === 'Inactivo') {
            return ApiResponse::error('El estudiante está inactivo y no puede crear una cuenta.', 409, null, 'conflict');
        }

        $correosValidos = array_filter([
            strtolower($estudiante->email_personal ?? ''),
            strtolower($estudiante->email_institucional ?? ''),
        ]);

        if (!in_array($correo, $correosValidos, true)) {
            return ApiResponse::error('El correo no coincide con el registro del estudiante.', 409, null, 'conflict');
        }

        $existeUsuario = DB::table('users')->where('email', $correo)->exists();
        if ($existeUsuario) {
            return ApiResponse::error('Ya existe una cuenta con ese correo.', 409, null, 'conflict');
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

        return ApiResponse::success(null, 'Cuenta creada correctamente. Ya puede iniciar sesion.', 201);
    }
}
