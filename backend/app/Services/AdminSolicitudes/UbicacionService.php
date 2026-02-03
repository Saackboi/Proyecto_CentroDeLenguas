<?php

// Servicio para la gestión de ubicaciones en solicitudes administrativas

namespace App\Services\AdminSolicitudes;

use App\Mail\EstudianteCredencialesMail;
use App\Services\SaldoService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UbicacionService
{
    use AdminSolicitudesHelpers;

    public function __construct(private SaldoService $saldoService)
    {
    }

    public function aprobarUbicacion(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'id_estudiante' => ['required', 'string', 'max:30'],
            'nivel' => ['nullable', 'string', 'max:10'],
            'tipo_id' => ['required', 'string', 'max:15'],
            'nombre' => ['required', 'string', 'max:50'],
            'apellido' => ['required', 'string', 'max:50'],
            'correo_personal' => ['required', 'email', 'max:100'],
            'correo_utp' => ['nullable', 'email', 'max:100'],
            'telefono' => ['required', 'string', 'max:20'],
            'estado' => ['required', 'in:Activo,Inactivo'],
            'saldo_pendiente' => ['nullable', 'numeric'],
            'es_estudiante' => ['required', 'in:SI,NO'],
        ]);

        $correoPersonal = strtolower(trim($validated['correo_personal']));
        $correoUtp = $validated['correo_utp'] ? strtolower(trim($validated['correo_utp'])) : null;
        $isUtp = $validated['es_estudiante'] === 'SI';

        DB::beginTransaction();
        $credencialesEnviadas = false;

        try {
            $estudiante = DB::table('students')
                ->where('id', $validated['id_estudiante'])
                ->where('type', 'regular')
                ->select('id', 'person_id')
                ->first();

            if (!$estudiante) {
                DB::rollBack();
                return ApiResponse::notFound('Estudiante no encontrado.');
            }

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
                ->where('id', $validated['id_estudiante'])
                ->update([
                    'level' => $validated['nivel'],
                    'status' => $validated['estado'],
                    'is_utp' => $isUtp,
                    'updated_at' => now(),
                ]);

            DB::table('payments')
                ->where('student_id', $validated['id_estudiante'])
                ->where('payment_type', 'PruebaUbicacion')
                ->where('status', 'Pendiente')
                ->update([
                    'amount' => 10.00,
                    'status' => 'Aceptado',
                    'updated_at' => now(),
                ]);

            $correoCuenta = $correoUtp ?: $correoPersonal;
            $passwordTemporal = $this->crearCuentaEstudiante($validated['id_estudiante'], $correoCuenta);
            if ($passwordTemporal) {
                try {
                    Mail::to($correoCuenta)->send(new EstudianteCredencialesMail($correoCuenta, $passwordTemporal));
                    $credencialesEnviadas = true;
                } catch (\Throwable $e) {
                    $credencialesEnviadas = false;
                }
            }

            $saldoObjetivo = $validated['saldo_pendiente'];
            if ($validated['estado'] === 'Inactivo') {
                $saldoObjetivo = 0.00;
            }

            if ($saldoObjetivo !== null) {
                $saldoActual = $this->saldoService->calcularSaldo($validated['id_estudiante']);
                $diferencia = round((float) $saldoObjetivo - $saldoActual, 2);

                if (abs($diferencia) >= 0.01) {
                    $this->saldoService->crearMovimientoSaldo(
                        $validated['id_estudiante'],
                        'ajuste',
                        $diferencia,
                        'ajuste_admin',
                        null,
                        null
                    );
                }
            }

            $this->crearNotificacion(
                $validated['id_estudiante'],
                'Solicitud aprobada',
                'Tu solicitud de prueba de ubicacion fue aprobada.',
                'estado'
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al actualizar los datos. Inténtelo nuevamente.');
        }

        return ApiResponse::success(['credenciales_enviadas' => $credencialesEnviadas], 'OK');
    }

    public function listarUbicacion()
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $registros = DB::table('students as s')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->leftJoin('payments as pay', function ($join) {
                $join->on('s.id', '=', 'pay.student_id')
                    ->where('pay.payment_type', '=', 'PruebaUbicacion')
                    ->where('pay.status', '=', 'Pendiente');
            })
            ->where('s.type', 'regular')
            ->whereIn('s.status', ['En proceso', 'En prueba'])
            ->select(
                's.id as id_estudiante',
                DB::raw('null as tipo_id'),
                'p.first_name as nombre',
                'p.last_name as apellido',
                'p.email_personal as correo_personal',
                'p.email_institucional as correo_utp',
                'p.phone as telefono',
                's.created_at as fecha_registro',
                's.status as estado_estudiante',
                'pay.payment_type as tipo_pago',
                'pay.receipt_path as comprobante_imagen',
                'pay.method as metodo_pago',
                'pay.bank as banco',
                'pay.account_owner as propietario_cuenta',
                'pay.status as estado_pago'
            )
            ->orderBy('s.id')
            ->get();

        return ApiResponse::success($registros);
    }

    public function rechazarUbicacion(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'id_estudiante' => ['required', 'string', 'max:30'],
            'motivo' => ['required', 'string', 'max:255'],
        ]);

        $motivo = trim($validated['motivo']);

        DB::beginTransaction();

        try {
            $actualizadas = DB::table('students')
                ->where('id', $validated['id_estudiante'])
                ->where('type', 'regular')
                ->update([
                    'status' => 'Inactivo',
                    'updated_at' => now(),
                ]);

            if ($actualizadas === 0) {
                DB::rollBack();
                return ApiResponse::notFound('Estudiante no encontrado.');
            }

            DB::table('payments')
                ->where('student_id', $validated['id_estudiante'])
                ->where('payment_type', 'PruebaUbicacion')
                ->where('status', 'Pendiente')
                ->update([
                    'amount' => 10.00,
                    'status' => 'Aceptado',
                    'updated_at' => now(),
                ]);

            $this->crearNotificacion(
                $validated['id_estudiante'],
                'Solicitud rechazada',
                'Tu solicitud de prueba de ubicacion fue rechazada. Motivo: ' . $motivo,
                'estado'
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al actualizar los datos. Inténtelo nuevamente.');
        }

        return ApiResponse::success(null, 'NOregistro');
    }
}
