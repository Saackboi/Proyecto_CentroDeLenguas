<?php

namespace App\Services;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VeranoService
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
            return ApiResponse::error('Debe ingresar al menos el nombre del padre o madre.', 422, null, 'validation_error');
        }

        if (empty($validated['celular_padre']) && empty($validated['celular_madre'])) {
            return ApiResponse::error('Debe ingresar al menos un celular del padre o madre.', 422, null, 'validation_error');
        }

        $idEstudiante = trim($validated['cedula']);

        $existe = DB::table('students')
            ->where('id', $idEstudiante)
            ->exists();

        if ($existe) {
            return ApiResponse::error('El número de identificación ya pertenece a un estudiante.', 409, null, 'conflict');
        }

        $archivoFirma = $request->file('firma_familiar');
        $archivoCedFamiliar = $request->file('ced_familiar');
        $archivoCedEst = $request->file('ced_est');

        $fechaActual = now()->format('Ymd_His');
        $rutaDestino = 'verano';

        $nombreFirma = 'firma_familiar_' . $idEstudiante . '_' . $fechaActual . '_' . Str::random(6) . '.' . $archivoFirma->getClientOriginalExtension();
        $nombreCedFamiliar = 'ced_familiar_' . $idEstudiante . '_' . $fechaActual . '_' . Str::random(6) . '.' . $archivoCedFamiliar->getClientOriginalExtension();
        $nombreCedEst = null;

        if ($archivoCedEst) {
            $nombreCedEst = 'ced_est_' . $idEstudiante . '_' . $fechaActual . '_' . Str::random(6) . '.' . $archivoCedEst->getClientOriginalExtension();
        }

        $rutaFirma = '/storage/verano/' . $nombreFirma;
        $rutaCedFamiliar = '/storage/verano/' . $nombreCedFamiliar;
        $rutaCedEst = $nombreCedEst ? '/storage/verano/' . $nombreCedEst : null;

        DB::beginTransaction();

        try {
            $personId = DB::table('people')->insertGetId([
                'first_name' => trim($validated['nombre_completo']),
                'last_name' => '',
                'phone' => trim($validated['numero_celular']),
                'email_personal' => trim($validated['correo_electronico']),
                'email_institucional' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('students')->insert([
                'id' => $idEstudiante,
                'person_id' => $personId,
                'type' => 'verano',
                'status' => 'En proceso',
                'level' => null,
                'is_utp' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('student_profiles')->insert([
                'student_id' => $idEstudiante,
                'birth_date' => $validated['fecha_nacimiento'],
                'home_number' => $validated['numero_casa'],
                'address' => trim($validated['domicilio']),
                'gender' => $validated['sexo'],
                'school' => trim($validated['nombre_colegio']),
                'signature_path' => $rutaFirma,
                'guardian_id_path' => $rutaCedFamiliar,
                'student_id_path' => $rutaCedEst,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('guardians')->insert([
                'student_id' => $idEstudiante,
                'father_name' => $validated['nombre_padre'] ?? '',
                'father_workplace' => $validated['lugar_trabajo_padre'] ?? '',
                'father_work_phone' => $validated['telefono_trabajo_padre'] ?? '',
                'father_phone' => $validated['celular_padre'] ?? '',
                'mother_name' => $validated['nombre_madre'] ?? '',
                'mother_workplace' => $validated['lugar_trabajo_madre'] ?? '',
                'mother_work_phone' => $validated['telefono_trabajo_madre'] ?? '',
                'mother_phone' => $validated['celular_madre'] ?? '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('student_contacts')->insert([
                'student_id' => $idEstudiante,
                'allergies' => $validated['alergico'] ?? 'No',
                'blood_type' => $validated['tipo_sangre'],
                'emergency_name' => $validated['contacto_urgencias'] ?? 'No indicado',
                'emergency_phone' => $validated['telefono_urgencias'] ?? '0000-0000',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Storage::disk('public')->putFileAs($rutaDestino, $archivoFirma, $nombreFirma);
            Storage::disk('public')->putFileAs($rutaDestino, $archivoCedFamiliar, $nombreCedFamiliar);
            if ($archivoCedEst) {
                Storage::disk('public')->putFileAs($rutaDestino, $archivoCedEst, $nombreCedEst);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al enviar datos del estudiante. Inténtelo nuevamente.');
        }

        return ApiResponse::success(null, 'Formulario enviado exitosamente.');
    }
}
