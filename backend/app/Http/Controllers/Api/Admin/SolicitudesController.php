<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\EstudianteCredencialesMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SolicitudesController extends Controller
{
    private function asegurarAdmin()
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->tipo_usuario !== 'Admin') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return null;
    }

    private function crearNotificacion(string $idEstudiante, string $titulo, string $cuerpo, string $tipo = 'estado'): void
    {
        DB::table('notificaciones')->insert([
            'id_estudiante' => $idEstudiante,
            'titulo' => $titulo,
            'cuerpo' => $cuerpo,
            'tipo' => $tipo,
            'leida' => false,
            'leida_en' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function crearCuentaEstudiante(string $idEstudiante, string $correo): ?string
    {
        $correo = strtolower(trim($correo));

        if ($correo === '') {
            return null;
        }

        $existe = DB::table('usuarios')
            ->where('id_estudiante', $idEstudiante)
            ->orWhere('correo', $correo)
            ->exists();

        if ($existe) {
            return null;
        }

        $passwordPlano = Str::random(10);

        DB::table('usuarios')->insert([
            'correo' => $correo,
            'id_estudiante' => $idEstudiante,
            'contrasena' => Hash::make($passwordPlano),
            'tipo_usuario' => 'Estudiante',
            'token_recuperacion' => null,
            'expiracion_token' => null,
        ]);

        return $passwordPlano;
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
            'deuda_total' => ['nullable', 'numeric'],
            'saldo_pendiente' => ['nullable', 'numeric'],
            'es_estudiante' => ['required', 'in:SI,NO'],
        ]);

        $deuda = $validated['deuda_total'];
        $saldo = $validated['saldo_pendiente'];

        if (strtolower($validated['estado']) === 'inactivo') {
            $deuda = null;
            $saldo = null;
        } elseif ($deuda === null || $saldo === null) {
            $montoBase = $validated['es_estudiante'] === 'SI' ? 90.00 : 100.00;
            $deuda = $deuda ?? $montoBase;
            $saldo = $saldo ?? $montoBase;
        }

        DB::beginTransaction();
        $credencialesEnviadas = false;

        try {
            $actualizadas = DB::table('estudiantes')
                ->where('id_estudiante', $validated['id_estudiante'])
                ->update([
                    'nivel' => $validated['nivel'],
                    'tipo_id' => $validated['tipo_id'],
                    'nombre' => $validated['nombre'],
                    'apellido' => $validated['apellido'],
                    'correo_personal' => $validated['correo_personal'],
                    'correo_utp' => $validated['correo_utp'],
                    'telefono' => $validated['telefono'],
                    'estado' => $validated['estado'],
                    'deuda_total' => $deuda,
                    'saldo_pendiente' => $saldo,
                    'es_estudiante' => $validated['es_estudiante'],
                ]);

            if ($actualizadas === 0) {
                DB::rollBack();
                return response()->json(['message' => 'Estudiante no encontrado.'], 404);
            }

            DB::table('pagos')
                ->where('id_estudiante', $validated['id_estudiante'])
                ->where('tipo_pago', 'PruebaUbicacion')
                ->where('estado', 'Pendiente')
                ->update([
                    'monto' => 10.00,
                    'estado' => 'Aceptado',
                ]);

            $correoCuenta = $validated['correo_utp'] ?: $validated['correo_personal'];
            $passwordTemporal = $this->crearCuentaEstudiante($validated['id_estudiante'], $correoCuenta);
            if ($passwordTemporal) {
                try {
                    Mail::to($correoCuenta)->send(new EstudianteCredencialesMail($correoCuenta, $passwordTemporal));
                    $credencialesEnviadas = true;
                } catch (\Throwable $e) {
                    $credencialesEnviadas = false;
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

            return response()->json([
                'message' => 'Error al actualizar los datos. Inténtelo nuevamente.',
            ], 500);
        }

        return response()->json([
            'message' => 'OK',
            'credenciales_enviadas' => $credencialesEnviadas,
        ]);
    }

    public function listarUbicacion()
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $registros = DB::table('estudiantes as e')
            ->leftJoin('pagos as p', function ($join) {
                $join->on('e.id_estudiante', '=', 'p.id_estudiante')
                    ->where('p.tipo_pago', '=', 'PruebaUbicacion')
                    ->where('p.estado', '=', 'Pendiente');
            })
            ->whereIn('e.estado', ['En proceso', 'En prueba'])
            ->select(
                'e.id_estudiante',
                'e.tipo_id',
                'e.nombre',
                'e.apellido',
                'e.correo_personal',
                'e.correo_utp',
                'e.telefono',
                'e.fecha_registro',
                'e.estado as estado_estudiante',
                'p.tipo_pago',
                'p.comprobante_imagen',
                'p.metodo_pago',
                'p.banco',
                'p.propietario_cuenta',
                'p.estado as estado_pago'
            )
            ->orderBy('e.id_estudiante')
            ->get();

        return response()->json($registros);
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
        $credencialesEnviadas = false;

        try {
            $actualizadas = DB::table('estudiantes')
                ->where('id_estudiante', $validated['id_estudiante'])
                ->update(['estado' => 'Inactivo']);

            if ($actualizadas === 0) {
                DB::rollBack();
                return response()->json(['message' => 'Estudiante no encontrado.'], 404);
            }

            DB::table('pagos')
                ->where('id_estudiante', $validated['id_estudiante'])
                ->where('tipo_pago', 'PruebaUbicacion')
                ->where('estado', 'Pendiente')
                ->update([
                    'monto' => 10.00,
                    'estado' => 'Aceptado',
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

            return response()->json([
                'message' => 'Error al actualizar los datos. Inténtelo nuevamente.',
            ], 500);
        }

        return response()->json(['message' => 'NOregistro']);
    }

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

        try {
            $estudiante = DB::table('estudiante_verano')
                ->select('id_familiar')
                ->where('id_estudiante', $validated['id_estudiante'])
                ->first();

            if (!$estudiante) {
                DB::rollBack();
                return response()->json(['message' => 'Estudiante no encontrado.'], 404);
            }

            DB::table('familiar_verano')
                ->where('id_familiar', $estudiante->id_familiar)
                ->update([
                    'nombre_padre' => $validated['nombre_padre'] ?? '',
                    'lugar_trabajo_padre' => $validated['lugar_trabajo_padre'] ?? '',
                    'telefono_trabajo_padre' => $validated['telefono_trabajo_padre'] ?? '',
                    'celular_padre' => $validated['celular_padre'] ?? '',
                    'nombre_madre' => $validated['nombre_madre'] ?? '',
                    'lugar_trabajo_madre' => $validated['lugar_trabajo_madre'] ?? '',
                    'telefono_trabajo_madre' => $validated['telefono_trabajo_madre'] ?? '',
                    'celular_madre' => $validated['celular_madre'] ?? '',
                ]);

            DB::table('estudiante_verano')
                ->where('id_estudiante', $validated['id_estudiante'])
                ->update([
                    'nivel' => $validated['nivel'],
                    'estado' => $validated['estado'],
                    'nombre_completo' => $validated['nombre_completo'],
                    'celular' => $validated['celular'],
                    'fecha_nacimiento' => $validated['fecha_nacimiento'],
                    'numero_casa' => $validated['numero_casa'],
                    'domicilio' => $validated['domicilio'],
                    'sexo' => $validated['sexo'],
                    'correo' => $validated['correo'],
                    'colegio' => $validated['colegio'],
                ]);

            DB::table('estudiante_contacto')->updateOrInsert(
                ['id_estudiante' => $validated['id_estudiante']],
                [
                    'alergias' => $validated['alergias'] ?? 'No',
                    'tipo_sangre' => $validated['tipo_sangre'],
                    'contacto_nombre' => $validated['contacto_nombre'] ?? null,
                    'contacto_telefono' => $validated['contacto_telefono'] ?? null,
                ]
            );

            $passwordTemporal = $this->crearCuentaEstudiante($validated['id_estudiante'], $validated['correo']);
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

            return response()->json([
                'message' => 'Error al actualizar datos del estudiante. Inténtelo nuevamente.',
            ], 500);
        }

        return response()->json([
            'message' => 'OK',
            'credenciales_enviadas' => $credencialesEnviadas,
        ]);
    }

    public function listarVerano()
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $registros = DB::table('estudiante_verano as ev')
            ->join('estudiante_contacto as ec', 'ev.id_estudiante', '=', 'ec.id_estudiante')
            ->join('familiar_verano as fv', 'ev.id_familiar', '=', 'fv.id_familiar')
            ->where('ev.estado', 'En proceso')
            ->select(
                'ev.id_estudiante',
                'ev.nombre_completo',
                'ev.celular',
                'ev.fecha_nacimiento',
                'ev.numero_casa',
                'ev.domicilio',
                'ev.sexo',
                'ev.correo',
                'ev.colegio',
                'ev.fecha_registro',
                'ev.firma_familiar_imagen',
                'ev.cedula_familiar_imagen',
                'ev.cedula_estudiante_imagen',
                'fv.nombre_padre',
                'fv.lugar_trabajo_padre',
                'fv.telefono_trabajo_padre',
                'fv.celular_padre',
                'fv.nombre_madre',
                'fv.lugar_trabajo_madre',
                'fv.telefono_trabajo_madre',
                'fv.celular_madre',
                'ec.contacto_nombre',
                'ec.contacto_telefono',
                'ec.tipo_sangre',
                'ec.alergias'
            )
            ->orderBy('ev.id_estudiante')
            ->get();

        return response()->json($registros);
    }

    public function rechazarVerano(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'id_estudiante' => ['required', 'string', 'max:30'],
            'motivo' => ['required', 'string', 'max:255'],
        ]);

        $motivo = trim($validated['motivo']);

        $actualizadas = DB::table('estudiante_verano')
            ->where('id_estudiante', $validated['id_estudiante'])
            ->update(['estado' => 'Inactivo']);

        if ($actualizadas === 0) {
            return response()->json(['message' => 'Estudiante no encontrado.'], 404);
        }

        $this->crearNotificacion(
            $validated['id_estudiante'],
            'Solicitud de verano rechazada',
            'Tu solicitud para cursos de verano fue rechazada. Motivo: ' . $motivo,
            'verano'
        );

        return response()->json(['message' => 'NOregistro']);
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
            $actualizadas = DB::table('estudiantes')
                ->where('id_estudiante', $validated['id_estudiante'])
                ->update(['saldo_pendiente' => $validated['saldo_pendiente']]);

            if ($actualizadas === 0) {
                DB::rollBack();
                return response()->json(['message' => 'Estudiante no encontrado.'], 404);
            }

            $pago = DB::table('pagos')
                ->where('id_estudiante', $validated['id_estudiante'])
                ->where('tipo_pago', 'Abono')
                ->where('estado', 'Pendiente')
                ->orderByDesc('fecha_pago')
                ->first();

            if (!$pago) {
                DB::rollBack();
                return response()->json(['message' => 'No hay abono pendiente que aceptar.'], 404);
            }

            DB::table('pagos')
                ->where('id_pago', $pago->id_pago)
                ->update([
                    'monto' => $validated['abono'],
                    'estado' => 'Aceptado',
                ]);

            $this->crearNotificacion(
                $validated['id_estudiante'],
                'Abono aprobado',
                'Tu abono fue aprobado.',
                'abono'
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar el abono. Inténtelo nuevamente.',
            ], 500);
        }

        return response()->json(['message' => 'OKabono']);
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
            $pago = DB::table('pagos')
                ->where('id_estudiante', $validated['id_estudiante'])
                ->where('tipo_pago', 'Abono')
                ->where('estado', 'Pendiente')
                ->orderByDesc('fecha_pago')
                ->first();

            if (!$pago) {
                DB::rollBack();
                return response()->json(['message' => 'No hay abono pendiente que rechazar.'], 404);
            }

            DB::table('pagos')
                ->where('id_pago', $pago->id_pago)
                ->update(['estado' => 'Rechazado']);

            $this->crearNotificacion(
                $validated['id_estudiante'],
                'Abono rechazado',
                'Tu abono fue rechazado. Motivo: ' . $motivo,
                'abono'
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar el abono. Inténtelo nuevamente.',
            ], 500);
        }

        return response()->json(['message' => 'NOabono']);
    }

    public function listarAbonos()
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $registros = DB::table('pagos as p')
            ->join('estudiantes as e', 'e.id_estudiante', '=', 'p.id_estudiante')
            ->where('p.tipo_pago', 'Abono')
            ->where('p.estado', 'Pendiente')
            ->select(
                'p.id_pago',
                'p.id_estudiante',
                'e.nombre',
                'e.apellido',
                'e.correo_personal',
                'e.correo_utp',
                'e.telefono',
                'p.tipo_pago',
                'p.comprobante_imagen',
                'p.metodo_pago',
                'p.banco',
                'p.propietario_cuenta',
                'p.monto',
                'p.fecha_pago',
                'p.estado as estado_pago'
            )
            ->orderBy('p.fecha_pago', 'desc')
            ->get();

        return response()->json($registros);
    }
}
