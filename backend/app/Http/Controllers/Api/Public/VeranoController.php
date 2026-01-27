<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VeranoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cedula' => ['required', 'string', 'max:30'],
            'nombre_completo' => ['required', 'string', 'max:100'],
            'numero_celular' => ['required', 'string', 'max:20'],
            'fecha_nacimiento' => ['required', 'date'],
            'numero_casa' => ['nullable', 'string', 'max:10'],
            'domicilio' => ['required', 'string', 'max:100'],
            'sexo' => ['required', 'in:Masculino,Femenino'],
            'correo_electronico' => ['required', 'email', 'max:100'],
            'nombre_colegio' => ['required', 'string', 'max:100'],
            'tipo_sangre' => ['required', 'string', 'max:45'],

            'nombre_padre' => ['nullable', 'string', 'max:100'],
            'lugar_trabajo_padre' => ['nullable', 'string', 'max:100'],
            'telefono_trabajo_padre' => ['nullable', 'string', 'max:20'],
            'celular_padre' => ['nullable', 'string', 'max:20'],
            'nombre_madre' => ['nullable', 'string', 'max:100'],
            'lugar_trabajo_madre' => ['nullable', 'string', 'max:100'],
            'telefono_trabajo_madre' => ['nullable', 'string', 'max:20'],
            'celular_madre' => ['nullable', 'string', 'max:20'],

            'alergico' => ['nullable', 'string', 'max:255'],
            'contacto_urgencias' => ['nullable', 'string', 'max:100'],
            'telefono_urgencias' => ['nullable', 'string', 'max:20'],

            'firma_familiar' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'ced_familiar' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'ced_est' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if (empty($validated['nombre_padre']) && empty($validated['nombre_madre'])) {
            return response()->json([
                'message' => 'Debe ingresar al menos el nombre del padre o madre.',
            ], 422);
        }

        if (empty($validated['celular_padre']) && empty($validated['celular_madre'])) {
            return response()->json([
                'message' => 'Debe ingresar al menos un celular del padre o madre.',
            ], 422);
        }

        $idEstudiante = trim($validated['cedula']);

        $existeVerano = DB::table('estudiante_verano')
            ->where('id_estudiante', $idEstudiante)
            ->exists();

        if ($existeVerano) {
            return response()->json([
                'message' => 'Este estudiante ya está registrado en nuestro sistema.',
            ], 409);
        }

        $existeRegular = DB::table('estudiantes')
            ->where('id_estudiante', $idEstudiante)
            ->exists();

        if ($existeRegular) {
            return response()->json([
                'message' => 'El número de identificación ya pertenece a un estudiante.',
            ], 409);
        }

        $archivoFirma = $request->file('firma_familiar');
        $archivoCedFamiliar = $request->file('ced_familiar');
        $archivoCedEst = $request->file('ced_est');

        $fechaActual = now()->format('Ymd_His');
        $rutaDestino = public_path('uploads/verano');

        $nombreFirma = 'firma_familiar_' . $idEstudiante . '_' . $fechaActual . '_' . Str::random(6) . '.' . $archivoFirma->getClientOriginalExtension();
        $nombreCedFamiliar = 'ced_familiar_' . $idEstudiante . '_' . $fechaActual . '_' . Str::random(6) . '.' . $archivoCedFamiliar->getClientOriginalExtension();
        $nombreCedEst = null;

        if ($archivoCedEst) {
            $nombreCedEst = 'ced_est_' . $idEstudiante . '_' . $fechaActual . '_' . Str::random(6) . '.' . $archivoCedEst->getClientOriginalExtension();
        }

        $rutaFirma = '/uploads/verano/' . $nombreFirma;
        $rutaCedFamiliar = '/uploads/verano/' . $nombreCedFamiliar;
        $rutaCedEst = $nombreCedEst ? '/uploads/verano/' . $nombreCedEst : null;

        DB::beginTransaction();

        try {
            $idFamiliar = 'fam_' . Str::random(12);

            DB::table('familiar_verano')->insert([
                'id_familiar' => $idFamiliar,
                'nombre_padre' => $validated['nombre_padre'] ?? '',
                'lugar_trabajo_padre' => $validated['lugar_trabajo_padre'] ?? '',
                'telefono_trabajo_padre' => $validated['telefono_trabajo_padre'] ?? '',
                'celular_padre' => $validated['celular_padre'] ?? '',
                'nombre_madre' => $validated['nombre_madre'] ?? '',
                'lugar_trabajo_madre' => $validated['lugar_trabajo_madre'] ?? '',
                'telefono_trabajo_madre' => $validated['telefono_trabajo_madre'] ?? '',
                'celular_madre' => $validated['celular_madre'] ?? '',
            ]);

            DB::table('estudiante_verano')->insert([
                'id_estudiante' => $idEstudiante,
                'id_familiar' => $idFamiliar,
                'nivel' => null,
                'nombre_completo' => trim($validated['nombre_completo']),
                'celular' => trim($validated['numero_celular']),
                'fecha_nacimiento' => $validated['fecha_nacimiento'],
                'numero_casa' => $validated['numero_casa'],
                'domicilio' => trim($validated['domicilio']),
                'sexo' => $validated['sexo'],
                'correo' => trim($validated['correo_electronico']),
                'colegio' => trim($validated['nombre_colegio']),
                'firma_familiar_imagen' => $rutaFirma,
                'cedula_familiar_imagen' => $rutaCedFamiliar,
                'cedula_estudiante_imagen' => $rutaCedEst,
                'estado' => 'En proceso',
                'fecha_registro' => now(),
            ]);

            DB::table('estudiante_contacto')->insert([
                'id_estudiante' => $idEstudiante,
                'alergias' => $validated['alergico'] ?? 'No',
                'tipo_sangre' => $validated['tipo_sangre'],
                'contacto_nombre' => $validated['contacto_urgencias'] ?? 'No indicado',
                'contacto_telefono' => $validated['telefono_urgencias'] ?? '0000-0000',
            ]);

            $archivoFirma->move($rutaDestino, $nombreFirma);
            $archivoCedFamiliar->move($rutaDestino, $nombreCedFamiliar);
            if ($archivoCedEst) {
                $archivoCedEst->move($rutaDestino, $nombreCedEst);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al enviar datos del estudiante. Inténtelo nuevamente.',
            ], 500);
        }

        return response()->json([
            'message' => 'Formulario enviado exitosamente.',
        ]);
    }
}
