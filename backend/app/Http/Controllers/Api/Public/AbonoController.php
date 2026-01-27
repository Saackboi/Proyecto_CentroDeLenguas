<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AbonoController extends Controller
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

        $estudiante = DB::table('estudiantes')
            ->select('nombre', 'apellido', 'estado')
            ->where('id_estudiante', $idEstudiante)
            ->first();

        if (!$estudiante) {
            return response()->json([
                'message' => 'El estudiante no está registrado.',
            ], 404);
        }

        if (strcasecmp($estudiante->nombre, $validated['nombre']) !== 0
            || strcasecmp($estudiante->apellido, $validated['apellido']) !== 0) {
            return response()->json([
                'message' => 'El nombre o apellido no coinciden con el estudiante.',
            ], 409);
        }

        if ($estudiante->estado !== 'Activo') {
            return response()->json([
                'message' => 'El estudiante no está activo y no puede registrar abonos.',
            ], 409);
        }

        $abonoPendiente = DB::table('pagos')
            ->where('id_estudiante', $idEstudiante)
            ->where('tipo_pago', 'Abono')
            ->where('estado', 'Pendiente')
            ->exists();

        if ($abonoPendiente) {
            return response()->json([
                'message' => 'Ya tiene un abono pendiente. Debe esperar a que sea procesado.',
            ], 409);
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

            DB::table('pagos')->insert([
                'id_estudiante' => $idEstudiante,
                'tipo_pago' => 'Abono',
                'metodo_pago' => $metodoPago,
                'banco' => $banco,
                'propietario_cuenta' => $duenoCuenta,
                'comprobante_imagen' => $rutaWeb,
                'monto' => 0.00,
                'fecha_pago' => now(),
                'estado' => 'Pendiente',
            ]);

            $archivo->move($rutaDestino, $nombreArchivo);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al enviar datos del pago. Inténtelo nuevamente.',
            ], 500);
        }

        return response()->json([
            'message' => 'Abono registrado correctamente.',
        ]);
    }
}
