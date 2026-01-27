<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificacionesController extends Controller
{
    public function store(Request $request)
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->tipo_usuario !== 'Admin') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $validated = $request->validate([
            'id_estudiante' => ['required', 'string', 'max:30'],
            'titulo' => ['required', 'string', 'max:150'],
            'cuerpo' => ['required', 'string'],
            'tipo' => ['nullable', 'string', 'max:30'],
        ]);

        $id = DB::table('notificaciones')->insertGetId([
            'id_estudiante' => $validated['id_estudiante'],
            'titulo' => $validated['titulo'],
            'cuerpo' => $validated['cuerpo'],
            'tipo' => $validated['tipo'] ?? 'general',
            'leida' => false,
            'leida_en' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Notificacion creada.',
            'id_notificacion' => $id,
        ], 201);
    }

    public function index(Request $request)
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->tipo_usuario !== 'Admin') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $validated = $request->validate([
            'id_estudiante' => ['required', 'string', 'max:30'],
            'solo_no_leidas' => ['nullable', 'boolean'],
        ]);

        $query = DB::table('notificaciones')
            ->where('id_estudiante', $validated['id_estudiante'])
            ->orderByDesc('created_at');

        if (!empty($validated['solo_no_leidas'])) {
            $query->where('leida', false);
        }

        return response()->json($query->get());
    }

    public function marcarLeida(string $id)
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->tipo_usuario !== 'Admin') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $actualizadas = DB::table('notificaciones')
            ->where('id_notificacion', $id)
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
}
