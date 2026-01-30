<?php

namespace App\Services;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPromocionesService
{
    private function asegurarAdmin(): array
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->role !== 'Admin') {
            return [null, ApiResponse::forbidden('No autorizado.')];
        }

        return [$usuario, null];
    }

    private function saldosSubquery()
    {
        return DB::table('balance_movements')
            ->select(
                'student_id',
                DB::raw("sum(case when movement_type in ('charge','adjustment') then amount else 0 end) - sum(case when movement_type = 'payment' then amount else 0 end) as saldo")
            )
            ->groupBy('student_id');
    }

    private function obtenerElegiblesPorTipo(string $tipo)
    {
        $subquery = DB::table('enrollments as e')
            ->join('group_sessions as gs', 'gs.id', '=', 'e.group_session_id')
            ->select('e.student_id', DB::raw('max(gs.end_date) as max_end_date'))
            ->whereNotNull('gs.end_date')
            ->groupBy('e.student_id');

        $base = DB::table('enrollments as e')
            ->join('group_sessions as gs', 'gs.id', '=', 'e.group_session_id')
            ->joinSub($subquery, 'ult', function ($join) {
                $join->on('e.student_id', '=', 'ult.student_id')
                    ->on('gs.end_date', '=', 'ult.max_end_date');
            })
            ->join('students as s', 's.id', '=', 'e.student_id')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->join('groups as g', 'g.id', '=', 'gs.group_id')
            ->leftJoin('promotions as pr', function ($join) {
                $join->on('pr.student_id', '=', 's.id')
                    ->on('pr.group_session_id', '=', 'gs.id')
                    ->whereNull('pr.reverted_at');
            })
            ->whereNull('pr.id')
            ->where('s.type', $tipo)
            ->whereNotNull('e.final_grade')
            ->where('e.final_grade', '>=', 75)
            ->whereRaw('CAST(COALESCE(g.level, 0) AS UNSIGNED) < 12');

        if ($tipo === 'regular') {
            $saldos = $this->saldosSubquery();
            $base->leftJoinSub($saldos, 'saldo', function ($join) {
                $join->on('saldo.student_id', '=', 's.id');
            })->whereRaw('COALESCE(saldo.saldo, 0) <= 0');
        }

        return $base->select(
            's.id as id_estudiante',
            DB::raw("concat(p.first_name, ' ', p.last_name) as estudiante"),
            'g.level as nivel',
            'e.final_grade as nota_final',
            'gs.id as group_session_id',
            'g.id as id_grupo',
            'gs.end_date as fecha_cierre'
        )
            ->orderBy('estudiante')
            ->get();
    }

    private function resolverGroupSessionId(string $idEstudiante, ?string $idGrupo, ?int $idSesion): ?int
    {
        if ($idSesion) {
            return $idSesion;
        }

        if (!$idGrupo) {
            return null;
        }

        return DB::table('enrollments as e')
            ->join('group_sessions as gs', 'gs.id', '=', 'e.group_session_id')
            ->where('e.student_id', $idEstudiante)
            ->where('gs.group_id', $idGrupo)
            ->orderByDesc('gs.end_date')
            ->value('gs.id');
    }

    public function elegibles(Request $request)
    {
        [$usuario, $error] = $this->asegurarAdmin();
        if ($error) {
            return $error;
        }

        $validated = $request->validate([
            'tipo' => ['nullable', 'in:regular,verano'],
        ]);

        $tipo = $validated['tipo'] ?? null;
        $respuesta = [];

        if ($tipo === null || $tipo === 'regular') {
            $respuesta['regular'] = $this->obtenerElegiblesPorTipo('regular');
        }

        if ($tipo === null || $tipo === 'verano') {
            $respuesta['verano'] = $this->obtenerElegiblesPorTipo('verano');
        }

        return ApiResponse::success($respuesta);
    }

    public function aplicar(Request $request)
    {
        [$usuario, $error] = $this->asegurarAdmin();
        if ($error) {
            return $error;
        }

        $validated = $request->validate([
            'tipo' => ['required', 'in:regular,verano'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_estudiante' => ['required', 'string', 'max:30'],
            'items.*.id_grupo' => ['nullable', 'string', 'max:10'],
            'items.*.group_session_id' => ['nullable', 'integer', 'exists:group_sessions,id'],
        ]);

        $tipo = $validated['tipo'];
        $adminCorreo = $usuario ? $usuario->email : null;
        $resultados = [
            'promovidos' => [],
            'omitidos' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($validated['items'] as $item) {
                $idEstudiante = $item['id_estudiante'];
                $idGrupo = $item['id_grupo'] ?? null;
                $idSesion = isset($item['group_session_id']) ? (int) $item['group_session_id'] : null;

                $groupSessionId = $this->resolverGroupSessionId($idEstudiante, $idGrupo, $idSesion);

                if (!$groupSessionId) {
                    $resultados['omitidos'][] = ['id_estudiante' => $idEstudiante, 'razon' => 'grupo_no_valido'];
                    continue;
                }

                $yaExiste = DB::table('promotions')
                    ->where('student_id', $idEstudiante)
                    ->where('group_session_id', $groupSessionId)
                    ->whereNull('reverted_at')
                    ->exists();

                if ($yaExiste) {
                    $resultados['omitidos'][] = ['id_estudiante' => $idEstudiante, 'razon' => 'ya_promovido'];
                    continue;
                }

                $registro = DB::table('enrollments as e')
                    ->join('group_sessions as gs', 'gs.id', '=', 'e.group_session_id')
                    ->join('groups as g', 'g.id', '=', 'gs.group_id')
                    ->join('students as s', 's.id', '=', 'e.student_id')
                    ->select('e.final_grade', 'g.level', 's.type', 's.level as nivel_estudiante')
                    ->where('e.group_session_id', $groupSessionId)
                    ->where('e.student_id', $idEstudiante)
                    ->first();

                if (!$registro || $registro->type !== $tipo) {
                    $resultados['omitidos'][] = ['id_estudiante' => $idEstudiante, 'razon' => 'no_elegible'];
                    continue;
                }

                if ($registro->final_grade === null || (int) $registro->final_grade < 75) {
                    $resultados['omitidos'][] = ['id_estudiante' => $idEstudiante, 'razon' => 'no_elegible'];
                    continue;
                }

                if ($tipo === 'regular') {
                    $saldo = (float) $this->saldosSubquery()
                        ->where('student_id', $idEstudiante)
                        ->value('saldo');
                    if ($saldo > 0) {
                        $resultados['omitidos'][] = ['id_estudiante' => $idEstudiante, 'razon' => 'saldo_pendiente'];
                        continue;
                    }
                }

                $nivelActual = (int) ($registro->nivel_estudiante ?? $registro->level ?? 0);
                if ($nivelActual >= 12) {
                    $resultados['omitidos'][] = ['id_estudiante' => $idEstudiante, 'razon' => 'nivel_maximo'];
                    continue;
                }

                $nivelNuevo = (string) min(12, $nivelActual + 1);

                DB::table('students')
                    ->where('id', $idEstudiante)
                    ->update(['level' => $nivelNuevo]);

                DB::table('promotions')->insert([
                    'student_id' => $idEstudiante,
                    'group_session_id' => $groupSessionId,
                    'old_level' => (string) $nivelActual,
                    'new_level' => $nivelNuevo,
                    'approved_by' => $adminCorreo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $resultados['promovidos'][] = [
                    'id_estudiante' => $idEstudiante,
                    'nivel_anterior' => (string) $nivelActual,
                    'nivel_nuevo' => $nivelNuevo,
                    'group_session_id' => $groupSessionId,
                ];
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al aplicar promociones. Intente nuevamente.');
        }

        return ApiResponse::success($resultados);
    }

    public function revertir(Request $request)
    {
        [$usuario, $error] = $this->asegurarAdmin();
        if ($error) {
            return $error;
        }

        $validated = $request->validate([
            'id_promocion' => ['required', 'integer'],
        ]);

        $adminCorreo = $usuario ? $usuario->email : null;

        $promocion = DB::table('promotions')
            ->where('id', $validated['id_promocion'])
            ->whereNull('reverted_at')
            ->first();

        if (!$promocion) {
            return ApiResponse::notFound('Promocion no encontrada.');
        }

        DB::beginTransaction();

        try {
            DB::table('students')
                ->where('id', $promocion->student_id)
                ->update(['level' => $promocion->old_level]);

            DB::table('promotions')
                ->where('id', $promocion->id)
                ->update([
                    'reverted_at' => now(),
                    'reverted_by' => $adminCorreo,
                    'updated_at' => now(),
                ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al revertir promocion. Intente nuevamente.');
        }

        return ApiResponse::success(null, 'Promocion revertida.');
    }
}
