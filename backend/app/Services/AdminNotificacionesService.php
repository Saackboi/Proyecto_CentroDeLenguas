<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminNotificacionesService
{
    public function store(Request $request)
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->role !== 'Admin') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $validated = $request->validate([
            'id_estudiante' => ['required_without:student_id', 'string', 'max:30'],
            'student_id' => ['required_without:id_estudiante', 'string', 'max:30'],
            'titulo' => ['required', 'string', 'max:150'],
            'cuerpo' => ['required', 'string'],
            'tipo' => ['nullable', 'string', 'max:30'],
        ]);

        $studentId = $validated['id_estudiante'] ?? $validated['student_id'];

        $id = DB::table('notifications')->insertGetId([
            'student_id' => $studentId,
            'title' => $validated['titulo'],
            'body' => $validated['cuerpo'],
            'type' => $validated['tipo'] ?? 'general',
            'read_at' => null,
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
        if (!$usuario || $usuario->role !== 'Admin') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $validated = $request->validate([
            'id_estudiante' => ['required_without:student_id', 'string', 'max:30'],
            'student_id' => ['required_without:id_estudiante', 'string', 'max:30'],
            'solo_no_leidas' => ['nullable', 'boolean'],
        ]);

        $studentId = $validated['id_estudiante'] ?? $validated['student_id'];

        $query = DB::table('notifications')
            ->where('student_id', $studentId)
            ->orderByDesc('created_at');

        if (!empty($validated['solo_no_leidas'])) {
            $query->whereNull('read_at');
        }

        return response()->json($query->get());
    }

    public function marcarLeida(string $id)
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->role !== 'Admin') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $actualizadas = DB::table('notifications')
            ->where('id', $id)
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        if ($actualizadas === 0) {
            return response()->json(['message' => 'Notificacion no encontrada.'], 404);
        }

        return response()->json(['message' => 'Notificacion marcada como leida.']);
    }
}
