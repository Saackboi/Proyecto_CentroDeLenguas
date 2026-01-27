<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PruebaUbicacionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_type' => ['required', 'string', 'in:cedula,pasaporte'],
            'identidad' => ['required', 'string', 'max:30'],
            'nombre' => ['required', 'string', 'max:50'],
            'apellido' => ['required', 'string', 'max:50'],
            'correo_personal' => ['required', 'email', 'max:100'],
            'correo_utp' => ['nullable', 'email', 'max:100'],
            'telefono' => ['required', 'string', 'max:20'],
            'tipo_pago' => ['required', 'string', 'in:banco,caja'],
            'nombre_banco' => ['nullable', 'string', 'max:50'],
            'dueno_cuenta' => ['nullable', 'string', 'max:100'],
            'comprobante_pago' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        if ($validated['tipo_pago'] === 'banco') {
            if (empty($validated['nombre_banco']) || empty($validated['dueno_cuenta'])) {
                return response()->json([
                    'message' => 'Debe ingresar el banco y el dueño de la cuenta.',
                ], 422);
            }
        }

        $idEstudiante = trim($validated['identidad']);

        $existe = false;
        $estado = DB::table('estudiantes')
            ->where('id_estudiante', $idEstudiante)
            ->value('estado');

        if ($estado !== null) {
            if ($estado === 'En proceso') {
                return response()->json([
                    'message' => 'Tu solicitud se encuentra en proceso. Por favor, estar pendiente al correo electrónico.',
                ], 409);
            }

            if ($estado !== 'Inactivo') {
                return response()->json([
                    'message' => 'El número de identificación ya se encuentra registrado en nuestro sistema.',
                ], 409);
            }

            $existe = true;
        }

        $estadoVerano = DB::table('estudiante_verano')
            ->where('id_estudiante', $idEstudiante)
            ->value('estado');

        if ($estadoVerano !== null) {
            if ($estadoVerano === 'En proceso') {
                return response()->json([
                    'message' => 'Tu solicitud se encuentra en proceso. Por favor, estar pendiente al correo electrónico.',
                ], 409);
            }

            if ($estadoVerano !== 'Inactivo') {
                return response()->json([
                    'message' => 'El número de identificación ya se encuentra registrado en nuestro sistema.',
                ], 409);
            }

            $existe = true;
        }

        $archivo = $request->file('comprobante_pago');
        $extension = $archivo->getClientOriginalExtension();
        $fechaActual = now()->format('Ymd_His');
        $nombreArchivo = 'pb_' . $idEstudiante . '_' . $fechaActual . '_' . Str::random(6) . '.' . $extension;
        $rutaDestino = public_path('uploads');
        $rutaWeb = '/uploads/' . $nombreArchivo;

        DB::beginTransaction();

        try {
            if ($existe) {
                DB::table('estudiantes')
                    ->where('id_estudiante', $idEstudiante)
                    ->update([
                        'tipo_id' => $validated['id_type'],
                        'nombre' => trim($validated['nombre']),
                        'apellido' => trim($validated['apellido']),
                        'correo_personal' => trim($validated['correo_personal']),
                        'correo_utp' => $validated['correo_utp'],
                        'telefono' => trim($validated['telefono']),
                        'estado' => 'En proceso',
                        'fecha_registro' => now(),
                    ]);
            } else {
                DB::table('estudiantes')->insert([
                    'id_estudiante' => $idEstudiante,
                    'tipo_id' => $validated['id_type'],
                    'nombre' => trim($validated['nombre']),
                    'apellido' => trim($validated['apellido']),
                    'correo_personal' => trim($validated['correo_personal']),
                    'correo_utp' => $validated['correo_utp'],
                    'telefono' => trim($validated['telefono']),
                    'estado' => 'En proceso',
                    'fecha_registro' => now(),
                ]);
            }

            $metodoPago = $validated['tipo_pago'] === 'banco' ? 'Banca en Linea' : 'Caja';
            $banco = $validated['tipo_pago'] === 'banco' ? trim($validated['nombre_banco']) : 'Caja';
            $duenoCuenta = $validated['tipo_pago'] === 'banco' ? trim($validated['dueno_cuenta']) : 'No aplica';

            DB::table('pagos')->insert([
                'id_estudiante' => $idEstudiante,
                'tipo_pago' => 'PruebaUbicacion',
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
                'message' => 'Error al enviar datos del estudiante. Inténtelo nuevamente.',
            ], 500);
        }

        return response()->json(['message' => 'OK']);
    }
}
