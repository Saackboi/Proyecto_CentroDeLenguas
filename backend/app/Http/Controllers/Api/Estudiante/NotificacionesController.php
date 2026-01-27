<?php

namespace App\Http\Controllers\Api\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificacionesController extends Controller
{
    private const DIAS_RETENCION_LEIDAS = 30;

    private function limpiarLeidasAntiguas(string $idEstudiante): void
    {
        $limite = now()->subDays(self::DIAS_RETENCION_LEIDAS);

        DB::table('notificaciones')
            ->where('id_estudiante', $idEstudiante)
            ->where('leida', true)
            ->whereNotNull('leida_en')
            ->where('leida_en', '<=', $limite)
            ->delete();
    }

    public function index(Request $request)
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->tipo_usuario !== 'Estudiante') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        if (empty($usuario->id_estudiante)) {
            return response()->json(['message' => 'Usuario sin estudiante asociado.'], 409);
        }

        $this->limpiarLeidasAntiguas($usuario->id_estudiante);

        $validated = $request->validate([
            'solo_no_leidas' => ['nullable', 'boolean'],
        ]);

        $query = DB::table('notificaciones')
            ->where('id_estudiante', $usuario->id_estudiante)
            ->orderByDesc('created_at');

        if (!empty($validated['solo_no_leidas'])) {
            $query->where('leida', false);
        }

        return response()->json($query->get());
    }

    public function marcarLeida(string $id)
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->tipo_usuario !== 'Estudiante') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        if (empty($usuario->id_estudiante)) {
            return response()->json(['message' => 'Usuario sin estudiante asociado.'], 409);
        }

        $actualizadas = DB::table('notificaciones')
            ->where('id_notificacion', $id)
            ->where('id_estudiante', $usuario->id_estudiante)
            ->update([
                'leida' => true,
                'leida_en' => now(),
                'updated_at' => now(),
            ]);

        if ($actualizadas === 0) {
            return response()->json(['message' => 'Notificacion no encontrada.'], 404);
        }

        return response()->json(['message' => 'Notificacion marcada como leida.']);
    }

    public function marcarTodasLeidas()
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->tipo_usuario !== 'Estudiante') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        if (empty($usuario->id_estudiante)) {
            return response()->json(['message' => 'Usuario sin estudiante asociado.'], 409);
        }

        $actualizadas = DB::table('notificaciones')
            ->where('id_estudiante', $usuario->id_estudiante)
            ->where('leida', false)
            ->update([
                'leida' => true,
                'leida_en' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Notificaciones marcadas como leidas.',
            'cantidad' => $actualizadas,
        ]);
    }

    public function eliminar(string $id)
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->tipo_usuario !== 'Estudiante') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        if (empty($usuario->id_estudiante)) {
            return response()->json(['message' => 'Usuario sin estudiante asociado.'], 409);
        }

        $eliminadas = DB::table('notificaciones')
            ->where('id_notificacion', $id)
            ->where('id_estudiante', $usuario->id_estudiante)
            ->delete();

        if ($eliminadas === 0) {
            return response()->json(['message' => 'Notificacion no encontrada.'], 404);
        }

        return response()->json(['message' => 'Notificacion eliminada.']);
    }

    public function eliminarTodas()
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->tipo_usuario !== 'Estudiante') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        if (empty($usuario->id_estudiante)) {
            return response()->json(['message' => 'Usuario sin estudiante asociado.'], 409);
        }

        $eliminadas = DB::table('notificaciones')
            ->where('id_estudiante', $usuario->id_estudiante)
            ->delete();

        return response()->json([
            'message' => 'Notificaciones eliminadas.',
            'cantidad' => $eliminadas,
        ]);
    }
}
