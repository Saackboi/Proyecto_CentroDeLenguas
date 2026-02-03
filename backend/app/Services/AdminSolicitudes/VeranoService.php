<?php

// Servicio para la gestión de verano en solicitudes administrativas

namespace App\Services\AdminSolicitudes;

use App\Mail\EstudianteCredencialesMail;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class VeranoService
{
    use AdminSolicitudesHelpers;

    public function aprobarVerano(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'id_estudiante' => ['required', 'string', 'max:30'],
            'estado' => ['required', 'in:Activo,Inactivo,En proceso'],
            'nivel' => ['required', 'string', 'max:10'],
            'nombre_completo' => ['required', 'string', 'max:100'],
            'celular' => ['required', 'string', 'max:20'],
            'fecha_nacimiento' => ['required', 'date'],
            'numero_casa' => ['nullable', 'string', 'max:10'],
            'domicilio' => ['required', 'string', 'max:100'],
            'sexo' => ['required', 'in:Masculino,Femenino'],
            'correo' => ['required', 'email', 'max:100'],
            'colegio' => ['required', 'string', 'max:100'],
            'tipo_sangre' => ['required', 'string', 'max:45'],
            'nombre_madre' => ['nullable', 'string', 'max:100'],
            'lugar_trabajo_madre' => ['nullable', 'string', 'max:100'],
            'telefono_trabajo_madre' => ['nullable', 'string', 'max:20'],
            'celular_madre' => ['nullable', 'string', 'max:20'],
            'nombre_padre' => ['nullable', 'string', 'max:100'],
            'lugar_trabajo_padre' => ['nullable', 'string', 'max:100'],
            'telefono_trabajo_padre' => ['nullable', 'string', 'max:20'],
            'celular_padre' => ['nullable', 'string', 'max:20'],
            'alergias' => ['nullable', 'string', 'max:255'],
            'contacto_nombre' => ['nullable', 'string', 'max:100'],
            'contacto_telefono' => ['nullable', 'string', 'max:20'],
        ]);

        DB::beginTransaction();
        $credencialesEnviadas = false;

        try {
            $estudiante = DB::table('students')
                ->where('id', $validated['id_estudiante'])
                ->where('type', 'verano')
                ->select('id', 'person_id')
                ->first();

            if (!$estudiante) {
                DB::rollBack();
                return ApiResponse::notFound('Estudiante no encontrado.');
            }

            DB::table('people')
                ->where('id', $estudiante->person_id)
                ->update([
                    'first_name' => $validated['nombre_completo'],
                    'last_name' => '',
                    'phone' => $validated['celular'],
                    'email_personal' => strtolower(trim($validated['correo'])),
                    'email_institucional' => null,
                    'updated_at' => now(),
                ]);

            DB::table('students')
                ->where('id', $validated['id_estudiante'])
                ->update([
                    'level' => $validated['nivel'],
                    'status' => $validated['estado'],
                    'updated_at' => now(),
                ]);

            DB::table('student_profiles')->updateOrInsert(
                ['student_id' => $validated['id_estudiante']],
                [
                    'birth_date' => $validated['fecha_nacimiento'],
                    'home_number' => $validated['numero_casa'],
                    'address' => $validated['domicilio'],
                    'gender' => $validated['sexo'],
                    'school' => $validated['colegio'],
                    'updated_at' => now(),
                ]
            );

            DB::table('guardians')->updateOrInsert(
                ['student_id' => $validated['id_estudiante']],
                [
                    'father_name' => $validated['nombre_padre'] ?? '',
                    'father_workplace' => $validated['lugar_trabajo_padre'] ?? '',
                    'father_work_phone' => $validated['telefono_trabajo_padre'] ?? '',
                    'father_phone' => $validated['celular_padre'] ?? '',
                    'mother_name' => $validated['nombre_madre'] ?? '',
                    'mother_workplace' => $validated['lugar_trabajo_madre'] ?? '',
                    'mother_work_phone' => $validated['telefono_trabajo_madre'] ?? '',
                    'mother_phone' => $validated['celular_madre'] ?? '',
                    'updated_at' => now(),
                ]
            );

            DB::table('student_contacts')->updateOrInsert(
                ['student_id' => $validated['id_estudiante']],
                [
                    'allergies' => $validated['alergias'] ?? 'No',
                    'blood_type' => $validated['tipo_sangre'],
                    'emergency_name' => $validated['contacto_nombre'] ?? null,
                    'emergency_phone' => $validated['contacto_telefono'] ?? null,
                    'updated_at' => now(),
                ]
            );

            $passwordTemporal = $this->crearCuentaEstudiante($validated['correo']);
            if ($passwordTemporal) {
                try {
                    Mail::to($validated['correo'])->send(
                        new EstudianteCredencialesMail($validated['correo'], $passwordTemporal)
                    );
                    $credencialesEnviadas = true;
                } catch (\Throwable $e) {
                    $credencialesEnviadas = false;
                }
            }

            $this->crearNotificacion(
                $validated['id_estudiante'],
                'Solicitud de verano aprobada',
                'Tu solicitud para cursos de verano fue aprobada.',
                'verano'
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al actualizar datos del estudiante. Inténtelo nuevamente.');
        }

        return ApiResponse::success(['credenciales_enviadas' => $credencialesEnviadas], 'OK');
    }

    public function listarVerano()
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $registros = DB::table('students as s')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->leftJoin('student_profiles as sp', 'sp.student_id', '=', 's.id')
            ->leftJoin('guardians as g', 'g.student_id', '=', 's.id')
            ->leftJoin('student_contacts as sc', 'sc.student_id', '=', 's.id')
            ->where('s.type', 'verano')
            ->where('s.status', 'En proceso')
            ->select(
                's.id as id_estudiante',
                DB::raw("concat(p.first_name, ' ', p.last_name) as nombre_completo"),
                'p.phone as celular',
                'sp.birth_date as fecha_nacimiento',
                'sp.home_number as numero_casa',
                'sp.address as domicilio',
                'sp.gender as sexo',
                'p.email_personal as correo',
                'sp.school as colegio',
                's.created_at as fecha_registro',
                'sp.signature_path as firma_familiar_imagen',
                'sp.guardian_id_path as cedula_familiar_imagen',
                'sp.student_id_path as cedula_estudiante_imagen',
                'g.father_name as nombre_padre',
                'g.father_workplace as lugar_trabajo_padre',
                'g.father_work_phone as telefono_trabajo_padre',
                'g.father_phone as celular_padre',
                'g.mother_name as nombre_madre',
                'g.mother_workplace as lugar_trabajo_madre',
                'g.mother_work_phone as telefono_trabajo_madre',
                'g.mother_phone as celular_madre',
                'sc.emergency_name as contacto_nombre',
                'sc.emergency_phone as contacto_telefono',
                'sc.blood_type as tipo_sangre',
                'sc.allergies as alergias'
            )
            ->orderBy('s.id')
            ->get();

        return ApiResponse::success($registros);
    }

    public function rechazarVerano(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $this->validarRechazo($request);

        $motivo = trim($validated['motivo']);

        $actualizadas = DB::table('students')
            ->where('id', $validated['id_estudiante'])
            ->where('type', 'verano')
            ->update([
                'status' => 'Inactivo',
                'updated_at' => now(),
            ]);

        if ($actualizadas === 0) {
            return ApiResponse::notFound('Estudiante no encontrado.');
        }

        $this->crearNotificacion(
            $validated['id_estudiante'],
            'Solicitud de verano rechazada',
            'Tu solicitud para cursos de verano fue rechazada. Motivo: ' . $motivo,
            'verano'
        );

        return ApiResponse::success(null, 'NOregistro');
    }
}
