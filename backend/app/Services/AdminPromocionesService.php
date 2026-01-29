<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPromocionesService
{
    private function asegurarAdmin()
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->tipo_usuario !== 'Admin') {
            return [null, response()->json(['message' => 'No autorizado.'], 403)];
        }

        return [$usuario, null];
    }

    private function ultimaInscripcionSubquery(string $tablaGrupo): string
    {
        return "(
            SELECT t1.id_estudiante, t1.id_grupo, t1.fecha_cierre
            FROM {$tablaGrupo} t1
            INNER JOIN (
                SELECT id_estudiante, MAX(fecha_cierre) AS max_cierre
                FROM {$tablaGrupo}
                WHERE fecha_cierre IS NOT NULL
                GROUP BY id_estudiante
            ) t2 ON t1.id_estudiante = t2.id_estudiante AND t1.fecha_cierre = t2.max_cierre
        ) as ult";
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
            $subquery = $this->ultimaInscripcionSubquery('grupos_estudiantes');
            $base = DB::table(DB::raw($subquery))
                ->join('grupos_estudiantes as ge', function ($join) {
                    $join->on('ge.id_estudiante', '=', 'ult.id_estudiante')
                        ->on('ge.id_grupo', '=', 'ult.id_grupo')
                        ->on('ge.fecha_cierre', '=', 'ult.fecha_cierre');
                })
                ->join('estudiantes as e', 'e.id_estudiante', '=', 'ge.id_estudiante')
                ->join('grupos as g', 'g.id_grupo', '=', 'ge.id_grupo')
                ->leftJoin('promociones as pr', function ($join) {
                    $join->on('pr.id_estudiante', '=', 'ge.id_estudiante')
                        ->on('pr.id_grupo', '=', 'ge.id_grupo')
                        ->where('pr.tipo', '=', 'regular')
                        ->whereNull('pr.revertido_en');
                })
                ->whereNull('pr.id')
                ->whereNotNull('ge.nota_final')
                ->where('ge.nota_final', '>=', 75)
                ->whereRaw('COALESCE(e.saldo_pendiente, 0) <= 0')
                ->whereRaw('CAST(COALESCE(g.nivel, 0) AS UNSIGNED) < 12')
                ->select(
                    'e.id_estudiante',
                    DB::raw("concat(e.nombre, ' ', e.apellido) as estudiante"),
                    'g.nivel',
                    'ge.nota_final',
                    'ge.id_grupo',
                    'ge.fecha_cierre'
                );

            $respuesta['regular'] = $base->orderBy('estudiante')->get();
        }

        if ($tipo === null || $tipo === 'verano') {
            $subquery = $this->ultimaInscripcionSubquery('grupos_estudiante_verano');
            $base = DB::table(DB::raw($subquery))
                ->join('grupos_estudiante_verano as ge', function ($join) {
                    $join->on('ge.id_estudiante', '=', 'ult.id_estudiante')
                        ->on('ge.id_grupo', '=', 'ult.id_grupo')
                        ->on('ge.fecha_cierre', '=', 'ult.fecha_cierre');
                })
                ->join('estudiante_verano as e', 'e.id_estudiante', '=', 'ge.id_estudiante')
                ->join('grupos as g', 'g.id_grupo', '=', 'ge.id_grupo')
                ->leftJoin('promociones as pr', function ($join) {
                    $join->on('pr.id_estudiante', '=', 'ge.id_estudiante')
                        ->on('pr.id_grupo', '=', 'ge.id_grupo')
                        ->where('pr.tipo', '=', 'verano')
                        ->whereNull('pr.revertido_en');
                })
                ->whereNull('pr.id')
                ->whereNotNull('ge.nota_final')
                ->where('ge.nota_final', '>=', 75)
                ->whereRaw('CAST(COALESCE(g.nivel, 0) AS UNSIGNED) < 12')
                ->select(
                    'e.id_estudiante',
                    'e.nombre_completo as estudiante',
                    'g.nivel',
                    'ge.nota_final',
                    'ge.id_grupo',
                    'ge.fecha_cierre'
                );

            $respuesta['verano'] = $base->orderBy('estudiante')->get();
        }

        return response()->json($respuesta);
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
            'items.*.id_grupo' => ['required', 'string', 'max:10'],
        ]);

        $tipo = $validated['tipo'];
        /** @var object $usuario */
        $adminCorreo = $usuario ? $usuario->correo : null;
        $resultados = [
            'promovidos' => [],
            'omitidos' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($validated['items'] as $item) {
                $idEstudiante = $item['id_estudiante'];
                $idGrupo = $item['id_grupo'];

                $yaExiste = DB::table('promociones')
                    ->where('id_estudiante', $idEstudiante)
                    ->where('id_grupo', $idGrupo)
                    ->where('tipo', $tipo)
                    ->whereNull('revertido_en')
                    ->exists();

                if ($yaExiste) {
                    $resultados['omitidos'][] = ['id_estudiante' => $idEstudiante, 'razon' => 'ya_promovido'];
                    continue;
                }

                $tablaGrupo = $tipo === 'verano' ? 'grupos_estudiante_verano' : 'grupos_estudiantes';
                $tablaEstudiante = $tipo === 'verano' ? 'estudiante_verano' : 'estudiantes';

                $registro = DB::table($tablaGrupo . ' as ge')
                    ->join('grupos as g', 'g.id_grupo', '=', 'ge.id_grupo')
                    ->where('ge.id_grupo', $idGrupo)
                    ->where('ge.id_estudiante', $idEstudiante)
                    ->select('ge.nota_final', 'g.nivel')
                    ->first();

                if (!$registro || $registro->nota_final === null || (int) $registro->nota_final < 75) {
                    $resultados['omitidos'][] = ['id_estudiante' => $idEstudiante, 'razon' => 'no_elegible'];
                    continue;
                }

                if ($tipo === 'regular') {
                    $saldo = (float) DB::table('estudiantes')
                        ->where('id_estudiante', $idEstudiante)
                        ->value('saldo_pendiente');
                    if ($saldo > 0) {
                        $resultados['omitidos'][] = ['id_estudiante' => $idEstudiante, 'razon' => 'saldo_pendiente'];
                        continue;
                    }
                }

                $nivelActual = (int) ($registro->nivel ?? 0);
                if ($nivelActual >= 12) {
                    $resultados['omitidos'][] = ['id_estudiante' => $idEstudiante, 'razon' => 'nivel_maximo'];
                    continue;
                }

                $nivelNuevo = (string) min(12, $nivelActual + 1);

                DB::table($tablaEstudiante)
                    ->where('id_estudiante', $idEstudiante)
                    ->update(['nivel' => $nivelNuevo]);

                DB::table('promociones')->insert([
                    'id_estudiante' => $idEstudiante,
                    'id_grupo' => $idGrupo,
                    'tipo' => $tipo,
                    'nivel_anterior' => (string) $nivelActual,
                    'nivel_nuevo' => $nivelNuevo,
                    'aprobado_por' => $adminCorreo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $resultados['promovidos'][] = [
                    'id_estudiante' => $idEstudiante,
                    'nivel_anterior' => (string) $nivelActual,
                    'nivel_nuevo' => $nivelNuevo,
                ];
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al aplicar promociones. Intente nuevamente.',
            ], 500);
        }

        return response()->json($resultados);
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

        /** @var object $usuario */
        $adminCorreo = $usuario ? $usuario->correo : null;

        $promocion = DB::table('promociones')
            ->where('id', $validated['id_promocion'])
            ->whereNull('revertido_en')
            ->first();

        if (!$promocion) {
            return response()->json(['message' => 'Promocion no encontrada.'], 404);
        }

        $tablaEstudiante = $promocion->tipo === 'verano' ? 'estudiante_verano' : 'estudiantes';

        DB::beginTransaction();

        try {
            DB::table($tablaEstudiante)
                ->where('id_estudiante', $promocion->id_estudiante)
                ->update(['nivel' => $promocion->nivel_anterior]);

            DB::table('promociones')
                ->where('id', $promocion->id)
                ->update([
                    'revertido_en' => now(),
                    'revertido_por' => $adminCorreo,
                    'updated_at' => now(),
                ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al revertir promocion. Intente nuevamente.',
            ], 500);
        }

        return response()->json(['message' => 'Promocion revertida.']);
    }
}
