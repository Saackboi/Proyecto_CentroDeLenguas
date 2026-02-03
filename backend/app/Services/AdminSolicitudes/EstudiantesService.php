<?php

namespace App\Services\AdminSolicitudes;

use App\Services\SaldoService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstudiantesService
{
    use AdminSolicitudesHelpers;

    public function __construct(private SaldoService $saldoService)
    {
    }

    public function detalleEstudiante(Request $request, string $id)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'tipo' => ['required', 'in:regular,verano'],
        ]);

        if ($validated['tipo'] === 'verano') {
            $datos = DB::table('students as s')
                ->join('people as p', 'p.id', '=', 's.person_id')
                ->leftJoin('student_profiles as sp', 'sp.student_id', '=', 's.id')
                ->leftJoin('guardians as g', 'g.student_id', '=', 's.id')
                ->leftJoin('student_contacts as sc', 'sc.student_id', '=', 's.id')
                ->where('s.id', $id)
                ->where('s.type', 'verano')
                ->select(
                    's.id as id_estudiante',
                    DB::raw("concat(p.first_name, ' ', p.last_name) as nombre_completo"),
                    's.status as estado',
                    's.level as nivel',
                    'p.phone as celular',
                    'sp.birth_date as fecha_nacimiento',
                    'sp.home_number as numero_casa',
                    'sp.address as domicilio',
                    'sp.gender as sexo',
                    'p.email_personal as correo',
                    'sp.school as colegio',
                    'sc.allergies as alergias',
                    'sc.blood_type as tipo_sangre',
                    'sc.emergency_name as contacto_nombre',
                    'sc.emergency_phone as contacto_telefono',
                    'g.father_name as nombre_padre',
                    'g.father_workplace as lugar_trabajo_padre',
                    'g.father_work_phone as telefono_trabajo_padre',
                    'g.father_phone as celular_padre',
                    'g.mother_name as nombre_madre',
                    'g.mother_workplace as lugar_trabajo_madre',
                    'g.mother_work_phone as telefono_trabajo_madre',
                    'g.mother_phone as celular_madre'
                )
                ->first();

            if (!$datos) {
                return ApiResponse::notFound('Estudiante no encontrado.');
            }

            return ApiResponse::success(['tipo' => 'verano', 'data' => $datos]);
        }

        $datos = DB::table('students as s')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->where('s.id', $id)
            ->where('s.type', 'regular')
            ->select(
                's.id as id_estudiante',
                DB::raw('null as tipo_id'),
                'p.first_name as nombre',
                'p.last_name as apellido',
                'p.email_personal as correo_personal',
                'p.email_institucional as correo_utp',
                'p.phone as telefono',
                's.level as nivel',
                's.status as estado',
                DB::raw("case when s.is_utp = 1 then 'SI' else 'NO' end as es_estudiante"),
                DB::raw('null as deuda_total')
            )
            ->first();

        if (!$datos) {
            return ApiResponse::notFound('Estudiante no encontrado.');
        }

        $saldo = $this->saldoService->calcularSaldo($id);

        return ApiResponse::success([
            'tipo' => 'regular',
            'data' => array_merge((array) $datos, [
                'saldo_pendiente' => $saldo,
                'deuda_total' => $saldo,
            ]),
        ]);
    }

    public function actualizarEstudiante(Request $request, string $id)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'tipo_id' => ['required', 'string', 'max:15'],
            'nombre' => ['required', 'string', 'max:50'],
            'apellido' => ['required', 'string', 'max:50'],
            'correo_personal' => ['required', 'email', 'max:100'],
            'correo_utp' => ['nullable', 'email', 'max:100'],
            'telefono' => ['required', 'string', 'max:20'],
            'nivel' => ['nullable', 'string', 'max:10'],
            'estado' => ['required', 'in:Activo,Inactivo,En proceso,En prueba'],
            'es_estudiante' => ['required', 'in:SI,NO'],
            'saldo_pendiente' => ['nullable', 'numeric'],
        ]);

        $estudiante = DB::table('students as s')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->where('s.id', $id)
            ->where('s.type', 'regular')
            ->select('s.id', 's.person_id', 's.status', 'p.email_personal', 'p.email_institucional')
            ->first();

        if (!$estudiante) {
            return ApiResponse::notFound('Estudiante no encontrado.');
        }

        $correoPersonal = strtolower(trim($validated['correo_personal']));
        $correoUtp = $validated['correo_utp'] ? strtolower(trim($validated['correo_utp'])) : null;
        $isUtp = $validated['es_estudiante'] === 'SI';

        DB::beginTransaction();

        try {
            DB::table('people')
                ->where('id', $estudiante->person_id)
                ->update([
                    'first_name' => $validated['nombre'],
                    'last_name' => $validated['apellido'],
                    'email_personal' => $correoPersonal,
                    'email_institucional' => $correoUtp,
                    'phone' => $validated['telefono'],
                    'updated_at' => now(),
                ]);

            DB::table('students')
                ->where('id', $id)
                ->update([
                    'level' => $validated['nivel'],
                    'status' => $validated['estado'],
                    'is_utp' => $isUtp,
                    'updated_at' => now(),
                ]);

            $correoCuenta = $correoUtp ?: $correoPersonal;
            $correoCuentaActual = strtolower(trim($estudiante->email_institucional ?? $estudiante->email_personal ?? ''));
            if ($correoCuentaActual !== '' && $correoCuenta !== $correoCuentaActual) {
                $existeCorreo = DB::table('users')
                    ->where('email', $correoCuenta)
                    ->exists();

                if ($existeCorreo) {
                    DB::rollBack();
                    return ApiResponse::error('El correo ya esta en uso.', 409, null, 'conflict');
                }

                DB::table('users')
                    ->where('email', $correoCuentaActual)
                    ->update(['email' => $correoCuenta]);
            }

            $saldoObjetivo = $validated['saldo_pendiente'];
            if ($validated['estado'] === 'Inactivo') {
                $saldoObjetivo = 0.00;
            }

            if ($saldoObjetivo !== null) {
                $saldoActual = $this->saldoService->calcularSaldo($id);
                $diferencia = round((float) $saldoObjetivo - $saldoActual, 2);

                if (abs($diferencia) >= 0.01) {
                    $this->saldoService->crearMovimientoSaldo(
                        $id,
                        'ajuste',
                        $diferencia,
                        'ajuste_admin',
                        null,
                        null
                    );
                }

                $this->saldoService->actualizarSaldoCache($id);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al actualizar el estudiante. Intente nuevamente.');
        }

        return ApiResponse::success(null, 'Estudiante actualizado.');
    }

    public function actualizarEstudianteVerano(Request $request, string $id)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'nivel' => ['nullable', 'string', 'max:10'],
            'estado' => ['required', 'in:Activo,Inactivo,En proceso'],
            'nombre_completo' => ['required', 'string', 'max:100'],
            'celular' => ['required', 'string', 'max:20'],
            'fecha_nacimiento' => ['required', 'date'],
            'numero_casa' => ['nullable', 'string', 'max:10'],
            'domicilio' => ['required', 'string', 'max:100'],
            'sexo' => ['required', 'in:Masculino,Femenino'],
            'correo' => ['required', 'email', 'max:100'],
            'colegio' => ['required', 'string', 'max:100'],
            'tipo_sangre' => ['required', 'string', 'max:45'],
            'alergias' => ['nullable', 'string', 'max:255'],
            'contacto_nombre' => ['nullable', 'string', 'max:100'],
            'contacto_telefono' => ['nullable', 'string', 'max:20'],
            'nombre_madre' => ['nullable', 'string', 'max:100'],
            'lugar_trabajo_madre' => ['nullable', 'string', 'max:100'],
            'telefono_trabajo_madre' => ['nullable', 'string', 'max:20'],
            'celular_madre' => ['nullable', 'string', 'max:20'],
            'nombre_padre' => ['nullable', 'string', 'max:100'],
            'lugar_trabajo_padre' => ['nullable', 'string', 'max:100'],
            'telefono_trabajo_padre' => ['nullable', 'string', 'max:20'],
            'celular_padre' => ['nullable', 'string', 'max:20'],
        ]);

        $estudiante = DB::table('students')
            ->where('id', $id)
            ->where('type', 'verano')
            ->select('id', 'person_id')
            ->first();

        if (!$estudiante) {
            return ApiResponse::notFound('Estudiante no encontrado.');
        }

        DB::beginTransaction();

        try {
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
                ->where('id', $id)
                ->update([
                    'level' => $validated['nivel'],
                    'status' => $validated['estado'],
                    'updated_at' => now(),
                ]);

            DB::table('student_profiles')->updateOrInsert(
                ['student_id' => $id],
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
                ['student_id' => $id],
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
                ['student_id' => $id],
                [
                    'allergies' => $validated['alergias'] ?? 'No',
                    'blood_type' => $validated['tipo_sangre'],
                    'emergency_name' => $validated['contacto_nombre'] ?? null,
                    'emergency_phone' => $validated['contacto_telefono'] ?? null,
                    'updated_at' => now(),
                ]
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al actualizar el estudiante. Intente nuevamente.');
        }

        return ApiResponse::success(null, 'Estudiante actualizado.');
    }
}
