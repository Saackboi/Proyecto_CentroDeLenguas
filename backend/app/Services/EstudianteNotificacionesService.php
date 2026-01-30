<?php

namespace App\Services;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstudianteNotificacionesService
{
    private const DIAS_RETENCION_LEIDAS = 30;

    private function resolverIdEstudiante(object $usuario): ?string
    {
        return DB::table('students as s')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->where(function ($query) use ($usuario) {
                $query->where('p.email_personal', $usuario->email)
                    ->orWhere('p.email_institucional', $usuario->email);
            })
            ->value('s.id');
    }

    private function limpiarLeidasAntiguas(string $idEstudiante): void
    {
        $limite = now()->subDays(self::DIAS_RETENCION_LEIDAS);

        DB::table('notifications')
            ->where('student_id', $idEstudiante)
            ->whereNotNull('read_at')
            ->where('read_at', '<=', $limite)
            ->delete();
    }

    public function index(Request $request)
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->role !== 'Estudiante') {
            return ApiResponse::forbidden('No autorizado.');
        }

        $idEstudiante = $this->resolverIdEstudiante($usuario);
        if (!$idEstudiante) {
            return ApiResponse::error('Usuario sin estudiante asociado.', 409, null, 'conflict');
        }

        $this->limpiarLeidasAntiguas($idEstudiante);

        $validated = $request->validate([
            'solo_no_leidas' => ['nullable', 'boolean'],
        ]);

        $query = DB::table('notifications')
            ->where('student_id', $idEstudiante)
            ->orderByDesc('created_at');

        if (!empty($validated['solo_no_leidas'])) {
            $query->whereNull('read_at');
        }

        return ApiResponse::success($query->get());
    }

    public function marcarLeida(string $id)
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->role !== 'Estudiante') {
            return ApiResponse::forbidden('No autorizado.');
        }

        $idEstudiante = $this->resolverIdEstudiante($usuario);
        if (!$idEstudiante) {
            return ApiResponse::error('Usuario sin estudiante asociado.', 409, null, 'conflict');
        }

        $actualizadas = DB::table('notifications')
            ->where('id', $id)
            ->where('student_id', $idEstudiante)
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        if ($actualizadas === 0) {
            return ApiResponse::notFound('Notificacion no encontrada.');
        }

        return ApiResponse::success(null, 'Notificacion marcada como leida.');
    }

    public function marcarTodasLeidas()
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->role !== 'Estudiante') {
            return ApiResponse::forbidden('No autorizado.');
        }

        $idEstudiante = $this->resolverIdEstudiante($usuario);
        if (!$idEstudiante) {
            return ApiResponse::error('Usuario sin estudiante asociado.', 409, null, 'conflict');
        }

        $actualizadas = DB::table('notifications')
            ->where('student_id', $idEstudiante)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return ApiResponse::success(['cantidad' => $actualizadas], 'Notificaciones marcadas como leidas.');
    }

    public function eliminar(string $id)
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->role !== 'Estudiante') {
            return ApiResponse::forbidden('No autorizado.');
        }

        $idEstudiante = $this->resolverIdEstudiante($usuario);
        if (!$idEstudiante) {
            return ApiResponse::error('Usuario sin estudiante asociado.', 409, null, 'conflict');
        }

        $eliminadas = DB::table('notifications')
            ->where('id', $id)
            ->where('student_id', $idEstudiante)
            ->delete();

        if ($eliminadas === 0) {
            return ApiResponse::notFound('Notificacion no encontrada.');
        }

        return ApiResponse::success(null, 'Notificacion eliminada.');
    }

    public function eliminarTodas()
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->role !== 'Estudiante') {
            return ApiResponse::forbidden('No autorizado.');
        }

        $idEstudiante = $this->resolverIdEstudiante($usuario);
        if (!$idEstudiante) {
            return ApiResponse::error('Usuario sin estudiante asociado.', 409, null, 'conflict');
        }

        $eliminadas = DB::table('notifications')
            ->where('student_id', $idEstudiante)
            ->delete();

        return ApiResponse::success(['cantidad' => $eliminadas], 'Notificaciones eliminadas.');
    }
}
