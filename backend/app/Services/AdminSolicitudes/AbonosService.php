<?php

// Servicio para la gestión de abonos en solicitudes administrativas


namespace App\Services\AdminSolicitudes;

use App\Services\SaldoService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbonosService
{
    use AdminSolicitudesHelpers;

    public function __construct(private SaldoService $saldoService)
    {
    }

    public function aprobarAbono(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'id_estudiante' => ['required', 'string', 'max:30'],
            'saldo_pendiente' => ['required', 'numeric'],
            'abono' => ['required', 'numeric'],
        ]);

        DB::beginTransaction();

        try {
            $pago = DB::table('payments')
                ->where('student_id', $validated['id_estudiante'])
                ->where('payment_type', 'Abono')
                ->where('status', 'Pendiente')
                ->orderByDesc('paid_at')
                ->first();

            if (!$pago) {
                DB::rollBack();
                return ApiResponse::notFound('No hay abono pendiente que aceptar.');
            }

            DB::table('payments')
                ->where('id', $pago->id)
                ->update([
                    'amount' => $validated['abono'],
                    'status' => 'Aceptado',
                    'updated_at' => now(),
                ]);

            $existeMovimiento = DB::table('balance_movements')
                ->where('payment_id', $pago->id)
                ->exists();

            if (!$existeMovimiento) {
                $this->saldoService->crearMovimientoSaldo(
                    $validated['id_estudiante'],
                    'abono',
                    (float) $validated['abono'],
                    'abono',
                    null,
                    (int) $pago->id
                );
            }

            $this->saldoService->actualizarSaldoCache($validated['id_estudiante']);

            $this->crearNotificacion(
                $validated['id_estudiante'],
                'Abono aprobado',
                'Tu abono fue aprobado.',
                'abono'
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al actualizar el abono. Inténtelo nuevamente.');
        }

        return ApiResponse::success(null, 'OKabono');
    }

    public function rechazarAbono(Request $request)
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
            $pago = DB::table('payments')
                ->where('student_id', $validated['id_estudiante'])
                ->where('payment_type', 'Abono')
                ->where('status', 'Pendiente')
                ->orderByDesc('paid_at')
                ->first();

            if (!$pago) {
                DB::rollBack();
                return ApiResponse::notFound('No hay abono pendiente que rechazar.');
            }

            DB::table('payments')
                ->where('id', $pago->id)
                ->update([
                    'status' => 'Rechazado',
                    'updated_at' => now(),
                ]);

            $this->crearNotificacion(
                $validated['id_estudiante'],
                'Abono rechazado',
                'Tu abono fue rechazado. Motivo: ' . $motivo,
                'abono'
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al actualizar el abono. Inténtelo nuevamente.');
        }

        return ApiResponse::success(null, 'NOabono');
    }

    public function listarAbonos()
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $registros = DB::table('payments as pay')
            ->join('students as s', 's.id', '=', 'pay.student_id')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->where('pay.payment_type', 'Abono')
            ->where('pay.status', 'Pendiente')
            ->select(
                'pay.id as id_pago',
                's.id as id_estudiante',
                'p.first_name as nombre',
                'p.last_name as apellido',
                'p.email_personal as correo_personal',
                'p.email_institucional as correo_utp',
                'p.phone as telefono',
                'pay.payment_type as tipo_pago',
                'pay.receipt_path as comprobante_imagen',
                'pay.method as metodo_pago',
                'pay.bank as banco',
                'pay.account_owner as propietario_cuenta',
                'pay.amount as monto',
                'pay.paid_at as fecha_pago',
                'pay.status as estado_pago'
            )
            ->orderBy('pay.paid_at', 'desc')
            ->get();

        return ApiResponse::success($registros);
    }
}
