<?php

// Servicio para la gestión de abonos públicos
// Permite registrar nuevos abonos de estudiantes.

namespace App\Services\Public;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AbonoService
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_type' => ['required', 'string', 'in:cedula,pasaporte'],
            'identidad' => ['required', 'string', 'max:30'],
            'nombre' => ['required', 'string', 'max:50'],
            'apellido' => ['required', 'string', 'max:50'],
            'tipo_pago' => ['required', 'string', 'in:banco,caja'],
            'nombre_banco' => ['required_if:tipo_pago,banco', 'nullable', 'string', 'max:50'],
            'dueno_cuenta' => ['required_if:tipo_pago,banco', 'nullable', 'string', 'max:100'],
            'comprobante_pago' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $idEstudiante = trim($validated['identidad']);

        $estudiante = DB::table('students as s')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->select('p.first_name', 'p.last_name', 's.status', 's.type')
            ->where('s.id', $idEstudiante)
            ->first();

        if (!$estudiante) {
            return ApiResponse::notFound('El estudiante no está registrado.');
        }

        if (strcasecmp($estudiante->first_name, $validated['nombre']) !== 0
            || strcasecmp($estudiante->last_name, $validated['apellido']) !== 0) {
            return ApiResponse::error('El nombre o apellido no coinciden con el estudiante.', 409, null, 'conflict');
        }

        if ($estudiante->status !== 'Activo') {
            return ApiResponse::error('El estudiante no está activo y no puede registrar abonos.', 409, null, 'conflict');
        }

        if ($estudiante->type !== 'regular') {
            return ApiResponse::error('Solo estudiantes regulares pueden registrar abonos.', 409, null, 'conflict');
        }

        $abonoPendiente = DB::table('payments')
            ->where('student_id', $idEstudiante)
            ->where('payment_type', 'Abono')
            ->where('status', 'Pendiente')
            ->exists();

        if ($abonoPendiente) {
            return ApiResponse::error('Ya tiene un abono pendiente. Debe esperar a que sea procesado.', 409, null, 'conflict');
        }

        $archivo = $request->file('comprobante_pago');
        $extension = $archivo->getClientOriginalExtension();
        $fechaActual = now()->format('Ymd_His');
        $nombreArchivo = 'ab_' . $idEstudiante . '_' . $fechaActual . '_' . Str::random(6) . '.' . $extension;
        $rutaDestino = public_path('uploads/abonos');
        $rutaWeb = '/uploads/abonos/' . $nombreArchivo;

        DB::beginTransaction();

        try {
            $metodoPago = $validated['tipo_pago'] === 'banco' ? 'Banca en Linea' : 'Caja';
            $banco = $validated['tipo_pago'] === 'banco' ? trim($validated['nombre_banco']) : 'Caja';
            $duenoCuenta = $validated['tipo_pago'] === 'banco' ? trim($validated['dueno_cuenta']) : 'No aplica';

            DB::table('payments')->insert([
                'student_id' => $idEstudiante,
                'payment_type' => 'Abono',
                'method' => $metodoPago,
                'bank' => $banco,
                'account_owner' => $duenoCuenta,
                'receipt_path' => $rutaWeb,
                'amount' => 0.00,
                'paid_at' => now(),
                'status' => 'Pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $archivo->move($rutaDestino, $nombreArchivo);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al enviar datos del pago. Inténtelo nuevamente.');
        }

        return ApiResponse::success(null, 'Abono registrado correctamente.');
    }
}
