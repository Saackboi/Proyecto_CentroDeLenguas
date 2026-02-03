<?php

// Servicio para la gestión de profesores en solicitudes administrativas

namespace App\Services\AdminSolicitudes;

use App\Mail\EstudianteResetPasswordMail;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProfesoresService
{
    use AdminSolicitudesHelpers;

    public function crearProfesor(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
            'apellido' => ['required', 'string', 'max:50'],
            'correo' => ['required', 'email', 'max:100'],
            'id_idioma' => ['required', 'string', 'max:15', 'exists:languages,id'],
            'estado' => ['nullable', 'in:Activo,Inactivo'],
        ]);

        $correo = $this->normalizarCorreo($validated['correo']);
        $estado = $validated['estado'] ?? 'Activo';

        if (DB::table('users')->where('email', $correo)->exists()) {
            return ApiResponse::error('El correo ya esta registrado.', 409, null, 'conflict');
        }

        DB::beginTransaction();
        $recuperacionEnviada = false;
        $teacherId = null;

        try {
            $passwordPlano = Str::random(10);
            $personId = DB::table('people')->insertGetId([
                'first_name' => $validated['nombre'],
                'last_name' => $validated['apellido'],
                'phone' => null,
                'email_personal' => $correo,
                'email_institucional' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')->insert([
                'email' => $correo,
                'password' => Hash::make($passwordPlano),
                'role' => 'Profesor',
                'token_recuperacion' => null,
                'expiracion_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $teacherId = DB::table('teachers')->insertGetId([
                'person_id' => $personId,
                'language_id' => $validated['id_idioma'],
                'status' => $estado,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            try {
                $token = Str::random(64);
                $expira = now()->addDay();

                DB::table('users')
                    ->where('email', $correo)
                    ->update([
                        'token_recuperacion' => $token,
                        'expiracion_token' => $expira,
                    ]);

                $link = rtrim(config('app.url'), '/') . '/profesores/reset?token=' . $token;

                Mail::to($correo)->send(new EstudianteResetPasswordMail($correo, $link, 'profesor', 'profesores'));
                $recuperacionEnviada = true;
            } catch (\Throwable $e) {
                $recuperacionEnviada = false;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al crear el profesor. Intente nuevamente.');
        }

        return ApiResponse::success([
            'id_profesor' => $teacherId,
            'correo' => $correo,
            'recuperacion_enviada' => $recuperacionEnviada,
        ], 'Profesor creado.', 201);
    }

    public function actualizarProfesor(Request $request, string $id)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
            'apellido' => ['required', 'string', 'max:50'],
            'correo' => ['required', 'email', 'max:100'],
            'id_idioma' => ['required', 'string', 'max:15', 'exists:languages,id'],
            'estado' => ['required', 'in:Activo,Inactivo'],
        ]);

        $profesor = DB::table('teachers as t')
            ->join('people as p', 'p.id', '=', 't.person_id')
            ->select('t.id', 't.person_id', 'p.email_personal')
            ->where('t.id', $id)
            ->first();

        if (!$profesor) {
            return ApiResponse::notFound('Profesor no encontrado.');
        }

        $nuevoCorreo = $this->normalizarCorreo($validated['correo']);
        $correoActual = $this->normalizarCorreo($profesor->email_personal ?? '');

        if ($nuevoCorreo !== $correoActual) {
            $correoEnUso = DB::table('users')
                ->where('email', $nuevoCorreo)
                ->exists();
            if ($correoEnUso) {
                return ApiResponse::error('El correo ya esta en uso.', 409, null, 'conflict');
            }
        }

        DB::beginTransaction();

        try {
            DB::table('teachers')
                ->where('id', $id)
                ->update([
                    'language_id' => $validated['id_idioma'],
                    'status' => $validated['estado'],
                    'updated_at' => now(),
                ]);

            DB::table('people')
                ->where('id', $profesor->person_id)
                ->update([
                    'first_name' => $validated['nombre'],
                    'last_name' => $validated['apellido'],
                    'email_personal' => $nuevoCorreo,
                    'updated_at' => now(),
                ]);

            if ($nuevoCorreo !== $correoActual) {
                DB::table('users')
                    ->where('email', $correoActual)
                    ->update(['email' => $nuevoCorreo]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al actualizar el profesor. Intente nuevamente.');
        }

        return ApiResponse::success(null, 'Profesor actualizado.');
    }

    public function detalleProfesor(string $id)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $profesor = DB::table('teachers as t')
            ->join('people as p', 'p.id', '=', 't.person_id')
            ->leftJoin('languages as l', 'l.id', '=', 't.language_id')
            ->where('t.id', $id)
            ->select(
                't.id as id_profesor',
                'p.first_name as nombre',
                'p.last_name as apellido',
                'p.email_personal as correo',
                't.status as estado',
                't.language_id as id_idioma',
                'l.name as idioma'
            )
            ->first();

        if (!$profesor) {
            return ApiResponse::notFound('Profesor no encontrado.');
        }

        return ApiResponse::success($profesor);
    }
}
