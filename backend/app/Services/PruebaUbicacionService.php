<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PruebaUbicacionService
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
        $estudiante = DB::table('students')
            ->select('status', 'person_id')
            ->where('id', $idEstudiante)
            ->first();

        if ($estudiante) {
            if ($estudiante->status === 'En proceso') {
                return response()->json([
                    'message' => 'Tu solicitud se encuentra en proceso. Por favor, estar pendiente al correo electrónico.',
                ], 409);
            }

            if ($estudiante->status !== 'Inactivo') {
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
                DB::table('people')
                    ->where('id', $estudiante->person_id)
                    ->update([
                        'first_name' => trim($validated['nombre']),
                        'last_name' => trim($validated['apellido']),
                        'phone' => trim($validated['telefono']),
                        'email_personal' => trim($validated['correo_personal']),
                        'email_institucional' => $validated['correo_utp'],
                        'updated_at' => now(),
                    ]);

                DB::table('students')
                    ->where('id', $idEstudiante)
                    ->update([
                        'type' => 'regular',
                        'status' => 'En proceso',
                        'is_utp' => !empty($validated['correo_utp']),
                        'updated_at' => now(),
                    ]);
            } else {
                $personId = DB::table('people')->insertGetId([
                    'first_name' => trim($validated['nombre']),
                    'last_name' => trim($validated['apellido']),
                    'phone' => trim($validated['telefono']),
                    'email_personal' => trim($validated['correo_personal']),
                    'email_institucional' => $validated['correo_utp'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('students')->insert([
                    'id' => $idEstudiante,
                    'person_id' => $personId,
                    'type' => 'regular',
                    'status' => 'En proceso',
                    'level' => null,
                    'is_utp' => !empty($validated['correo_utp']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $metodoPago = $validated['tipo_pago'] === 'banco' ? 'Banca en Linea' : 'Caja';
            $banco = $validated['tipo_pago'] === 'banco' ? trim($validated['nombre_banco']) : 'Caja';
            $duenoCuenta = $validated['tipo_pago'] === 'banco' ? trim($validated['dueno_cuenta']) : 'No aplica';

            DB::table('payments')->insert([
                'student_id' => $idEstudiante,
                'payment_type' => 'PruebaUbicacion',
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

            return response()->json([
                'message' => 'Error al enviar datos del estudiante. Inténtelo nuevamente.',
            ], 500);
        }

        return response()->json(['message' => 'OK']);
    }
}
