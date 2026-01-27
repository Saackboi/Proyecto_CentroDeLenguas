<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegistroEstudianteController extends Controller
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

        $estudiante = DB::table('estudiantes')
            ->select('correo_personal', 'correo_utp', 'estado')
            ->where('id_estudiante', $idEstudiante)
            ->first();

        $estudianteVerano = null;
        if (!$estudiante) {
            $estudianteVerano = DB::table('estudiante_verano')
                ->select('correo', 'estado')
                ->where('id_estudiante', $idEstudiante)
                ->first();
        }

        if (!$estudiante && !$estudianteVerano) {
            return response()->json([
                'message' => 'No existe un registro de estudiante con esa identificación.',
            ], 404);
        }

        $estado = $estudiante ? $estudiante->estado : $estudianteVerano->estado;
        if ($estado === 'Inactivo') {
            return response()->json([
                'message' => 'El estudiante está inactivo y no puede crear una cuenta.',
            ], 409);
        }

        if ($estudiante) {
            $correosValidos = array_filter([
                strtolower($estudiante->correo_personal ?? ''),
                strtolower($estudiante->correo_utp ?? ''),
            ]);
        } else {
            $correosValidos = [strtolower($estudianteVerano->correo ?? '')];
        }

        if (!in_array($correo, $correosValidos, true)) {
            return response()->json([
                'message' => 'El correo no coincide con el registro del estudiante.',
            ], 409);
        }

        $existeUsuario = DB::table('usuarios')->where('correo', $correo)->exists();
        if ($existeUsuario) {
            return response()->json([
                'message' => 'Ya existe una cuenta con ese correo.',
            ], 409);
        }

        DB::table('usuarios')->insert([
            'correo' => $correo,
            'id_estudiante' => $idEstudiante,
            'contrasena' => Hash::make($validated['contrasena']),
            'tipo_usuario' => 'Estudiante',
            'token_recuperacion' => null,
            'expiracion_token' => null,
        ]);

        return response()->json([
            'message' => 'Cuenta creada correctamente. Ya puede iniciar sesion.',
        ], 201);
    }
}
