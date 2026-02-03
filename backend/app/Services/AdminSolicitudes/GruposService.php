<?php

// Servicio para la gestión de grupos en solicitudes administrativas

namespace App\Services\AdminSolicitudes;

use App\Services\AdminReportService;
use App\Services\SaldoService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GruposService
{
    use AdminSolicitudesHelpers;

    public function __construct(
        private SaldoService $saldoService,
        private AdminReportService $reportService
    ) {
    }

    public function crearGrupo(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'nivel' => ['required', 'string', 'max:10'],
            'id_profesor' => ['required', 'integer', 'exists:teachers,id'],
            'aula' => ['required', 'string', 'max:50'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_cierre' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'tipo' => ['required', 'in:regular,verano'],
            'estudiantes' => ['required', 'array', 'min:1'],
            'estudiantes.*.id_estudiante' => ['required', 'string', 'max:30'],
        ]);

        $idsEstudiantes = collect($validated['estudiantes'])
            ->pluck('id_estudiante')
            ->unique()
            ->values()
            ->all();

        $totalEstudiantes = DB::table('students')
            ->whereIn('id', $idsEstudiantes)
            ->where('type', $validated['tipo'])
            ->count();

        if ($totalEstudiantes !== count($idsEstudiantes)) {
            return ApiResponse::error('Hay estudiantes que no existen en el sistema.', 422, null, 'validation_error');
        }

        $profesor = DB::table('teachers')
            ->select('language_id')
            ->where('id', $validated['id_profesor'])
            ->first();

        if (!$profesor) {
            return ApiResponse::notFound('Profesor no encontrado.');
        }

        $idGrupo = $this->generarIdGrupo();

        DB::beginTransaction();

        try {
            DB::table('groups')->insert([
                'id' => $idGrupo,
                'language_id' => $profesor->language_id,
                'level' => $validated['nivel'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $groupSessionId = DB::table('group_sessions')->insertGetId([
                'group_id' => $idGrupo,
                'teacher_id' => $validated['id_profesor'],
                'start_date' => $validated['fecha_inicio'],
                'end_date' => $validated['fecha_cierre'],
                'classroom' => $validated['aula'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $lotes = [];
            foreach ($idsEstudiantes as $idEstudiante) {
                $lotes[] = [
                    'student_id' => $idEstudiante,
                    'group_session_id' => $groupSessionId,
                    'final_grade' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if ($validated['tipo'] === 'regular') {
                    $monto = $this->saldoService->obtenerMontoMatricula($idEstudiante);
                    $this->saldoService->crearMovimientoSaldo(
                        $idEstudiante,
                        'cargo',
                        $monto,
                        'matricula',
                        $groupSessionId,
                        null
                    );
                    $this->saldoService->actualizarSaldoCache($idEstudiante);
                }
            }

            DB::table('enrollments')->insert($lotes);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al crear el grupo. Intente nuevamente.');
        }

        return ApiResponse::success(['id_grupo' => $idGrupo], 'Grupo creado.', 201);
    }

    public function actualizarGrupo(Request $request, string $id)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'nivel' => ['nullable', 'string', 'max:10'],
            'id_profesor' => ['required', 'integer', 'exists:teachers,id'],
            'aula' => ['nullable', 'string', 'max:50'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_cierre' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'tipo' => ['required', 'in:regular,verano'],
            'estudiantes_agregar' => ['nullable', 'array'],
            'estudiantes_agregar.*.id_estudiante' => ['required_with:estudiantes_agregar', 'string', 'max:30'],
            'estudiantes_eliminar' => ['nullable', 'array'],
            'estudiantes_eliminar.*.id_estudiante' => ['required_with:estudiantes_eliminar', 'string', 'max:30'],
        ]);

        $grupoExiste = DB::table('groups')
            ->where('id', $id)
            ->exists();

        if (!$grupoExiste) {
            return ApiResponse::notFound('Grupo no encontrado.');
        }

        $groupSession = DB::table('group_sessions')
            ->where('group_id', $id)
            ->orderByDesc('start_date')
            ->first();

        if (!$groupSession) {
            return ApiResponse::notFound('Grupo no encontrado.');
        }

        $idsAgregar = collect($validated['estudiantes_agregar'] ?? [])
            ->pluck('id_estudiante')
            ->unique()
            ->values()
            ->all();
        $idsEliminar = collect($validated['estudiantes_eliminar'] ?? [])
            ->pluck('id_estudiante')
            ->unique()
            ->values()
            ->all();

        if ($idsAgregar) {
            $totalAgregar = DB::table('students')
                ->whereIn('id', $idsAgregar)
                ->where('type', $validated['tipo'])
                ->count();
            if ($totalAgregar !== count($idsAgregar)) {
                return ApiResponse::error('Hay estudiantes para agregar que no existen en el sistema.', 422, null, 'validation_error');
            }
        }

        DB::beginTransaction();
        $ajustesPendientes = [];

        try {
            if (!empty($validated['nivel'])) {
                DB::table('groups')
                    ->where('id', $id)
                    ->update([
                        'level' => $validated['nivel'],
                        'updated_at' => now(),
                    ]);
            }

            $actualizarSesion = [
                'teacher_id' => $validated['id_profesor'],
                'start_date' => $validated['fecha_inicio'],
                'end_date' => $validated['fecha_cierre'],
                'updated_at' => now(),
            ];
            if (!empty($validated['aula'])) {
                $actualizarSesion['classroom'] = $validated['aula'];
            }

            DB::table('group_sessions')
                ->where('id', $groupSession->id)
                ->update($actualizarSesion);

            if ($idsEliminar) {
                foreach ($idsEliminar as $idEstudiante) {
                    DB::table('enrollments')
                        ->where('group_session_id', $groupSession->id)
                        ->where('student_id', $idEstudiante)
                        ->delete();

                    if ($validated['tipo'] === 'regular') {
                        $ajustesPendientes[] = [
                            'id_estudiante' => $idEstudiante,
                            'monto_sugerido' => $this->saldoService->obtenerMontoMatricula($idEstudiante),
                        ];
                    }
                }
            }

            if ($idsAgregar) {
                $lotes = [];
                foreach ($idsAgregar as $idEstudiante) {
                    $lotes[] = [
                        'student_id' => $idEstudiante,
                        'group_session_id' => $groupSession->id,
                        'final_grade' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if ($validated['tipo'] === 'regular') {
                        $monto = $this->saldoService->obtenerMontoMatricula($idEstudiante);
                        $existeCargo = DB::table('balance_movements')
                            ->where('student_id', $idEstudiante)
                            ->where('movement_type', 'charge')
                            ->where('reason', 'matricula')
                            ->where('group_session_id', $groupSession->id)
                            ->exists();

                        if (!$existeCargo) {
                            $this->saldoService->crearMovimientoSaldo(
                                $idEstudiante,
                                'cargo',
                                $monto,
                                'matricula',
                                $groupSession->id,
                                null
                            );
                        }

                        $this->saldoService->actualizarSaldoCache($idEstudiante);
                    }
                }

                DB::table('enrollments')->insertOrIgnore($lotes);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al actualizar el grupo. Intente nuevamente.');
        }

        return ApiResponse::success([
            'agregados' => count($idsAgregar),
            'eliminados' => count($idsEliminar),
            'ajustes_pendientes' => $ajustesPendientes,
        ], 'Grupo actualizado.');
    }

    public function previsualizarAjusteRetiro(Request $request, string $id)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'tipo' => ['required', 'in:regular,verano'],
            'id_estudiante' => ['required', 'string', 'max:30'],
        ]);

        if ($validated['tipo'] !== 'regular') {
            return ApiResponse::error('Los ajustes aplican solo a estudiantes regulares.', 422, null, 'validation_error');
        }

        $existeGrupo = DB::table('groups')->where('id', $id)->exists();
        if (!$existeGrupo) {
            return ApiResponse::notFound('Grupo no encontrado.');
        }

        $groupSession = DB::table('group_sessions')
            ->where('group_id', $id)
            ->orderByDesc('start_date')
            ->first();

        if (!$groupSession) {
            return ApiResponse::notFound('Grupo no encontrado.');
        }

        $enGrupo = DB::table('enrollments')
            ->where('group_session_id', $groupSession->id)
            ->where('student_id', $validated['id_estudiante'])
            ->exists();

        if (!$enGrupo) {
            return ApiResponse::notFound('Estudiante no pertenece al grupo.');
        }

        $monto = $this->saldoService->obtenerMontoMatricula($validated['id_estudiante']);
        $saldoActual = $this->saldoService->calcularSaldo($validated['id_estudiante']);
        $saldoNuevo = max(0.00, $saldoActual - $monto);

        return ApiResponse::success([
            'id_estudiante' => $validated['id_estudiante'],
            'id_grupo' => $id,
            'monto_sugerido' => $monto,
            'saldo_actual' => $saldoActual,
            'saldo_resultante' => $saldoNuevo,
        ]);
    }

    public function confirmarAjusteRetiro(Request $request, string $id)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'tipo' => ['required', 'in:regular,verano'],
            'id_estudiante' => ['required', 'string', 'max:30'],
        ]);

        if ($validated['tipo'] !== 'regular') {
            return ApiResponse::error('Los ajustes aplican solo a estudiantes regulares.', 422, null, 'validation_error');
        }

        $groupSession = DB::table('group_sessions')
            ->where('group_id', $id)
            ->orderByDesc('start_date')
            ->first();

        if (!$groupSession) {
            return ApiResponse::notFound('Grupo no encontrado.');
        }

        $enGrupo = DB::table('enrollments')
            ->where('group_session_id', $groupSession->id)
            ->where('student_id', $validated['id_estudiante'])
            ->exists();

        if ($enGrupo) {
            return ApiResponse::error('Debe retirar al estudiante del grupo antes de aplicar el ajuste.', 409, null, 'conflict');
        }

        $yaAjustado = DB::table('balance_movements')
            ->where('student_id', $validated['id_estudiante'])
            ->where('group_session_id', $groupSession->id)
            ->where('movement_type', 'adjustment')
            ->where('reason', 'retiro')
            ->exists();

        if ($yaAjustado) {
            return ApiResponse::error('El ajuste de retiro ya fue aplicado.', 409, null, 'conflict');
        }

        $monto = $this->saldoService->obtenerMontoMatricula($validated['id_estudiante']);
        $montoAjuste = -1 * $monto;

        DB::beginTransaction();

        try {
            $this->saldoService->crearMovimientoSaldo(
                $validated['id_estudiante'],
                'ajuste',
                $montoAjuste,
                'retiro',
                $groupSession->id,
                null
            );

            $this->saldoService->actualizarSaldoCache($validated['id_estudiante']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al aplicar el ajuste. Intente nuevamente.');
        }

        return ApiResponse::success(['monto' => $montoAjuste], 'Ajuste aplicado.');
    }

    public function listarGrupos(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $base = DB::table('group_sessions as gs')
            ->join('groups as g', 'g.id', '=', 'gs.group_id')
            ->join('teachers as t', 't.id', '=', 'gs.teacher_id')
            ->join('people as p', 'p.id', '=', 't.person_id')
            ->leftJoin('enrollments as e', 'e.group_session_id', '=', 'gs.id')
            ->leftJoin('students as s', 's.id', '=', 'e.student_id')
            ->select(
                'g.id as id_grupo',
                'gs.teacher_id as id_profesor',
                DB::raw("concat(p.first_name, ' ', p.last_name) as profesor"),
                'gs.classroom as aula',
                DB::raw('count(e.id) as total_estudiantes'),
                'gs.start_date as fecha_inicio',
                'gs.end_date as fecha_cierre',
                DB::raw("case when max(case when s.type = 'verano' then 1 else 0 end) = 1 then 'verano' else 'regular' end as tipo")
            )
            ->groupBy(
                'g.id',
                'gs.teacher_id',
                'p.first_name',
                'p.last_name',
                'gs.classroom',
                'gs.start_date',
                'gs.end_date'
            );

        $query = DB::query()->fromSub($base, 'g');

        return $this->reportService->datatableResponse(
            $request,
            $query,
            ['id_grupo', 'profesor', 'aula', 'fecha_inicio', 'fecha_cierre', 'tipo'],
            [
                'id_grupo' => 'id_grupo',
                'profesor' => 'profesor',
                'aula' => 'aula',
                'total_estudiantes' => 'total_estudiantes',
                'fecha_inicio' => 'fecha_inicio',
                'fecha_cierre' => 'fecha_cierre',
                'tipo' => 'tipo',
            ],
            ['column' => 'fecha_inicio', 'dir' => 'desc']
        );
    }

    public function detalleGrupo(Request $request, string $id)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'tipo' => ['required', 'in:regular,verano'],
        ]);

        $grupo = DB::table('group_sessions as gs')
            ->join('groups as g', 'g.id', '=', 'gs.group_id')
            ->join('teachers as t', 't.id', '=', 'gs.teacher_id')
            ->join('people as p', 'p.id', '=', 't.person_id')
            ->leftJoin('languages as l', 'l.id', '=', 'g.language_id')
            ->where('gs.group_id', $id)
            ->orderByDesc('gs.start_date')
            ->select(
                'g.id as id_grupo',
                'gs.teacher_id as id_profesor',
                DB::raw("concat(p.first_name, ' ', p.last_name) as profesor"),
                'g.level as nivel',
                'l.name as curso',
                'gs.start_date as fecha_inicio',
                'gs.end_date as fecha_cierre',
                'gs.classroom as aula',
                'gs.id as id_sesion'
            )
            ->first();

        if (!$grupo) {
            return ApiResponse::notFound('Grupo no encontrado.');
        }

        $estudiantes = $this->listarEstudiantesGrupoInterno($grupo->id_sesion, $validated['tipo']);

        return ApiResponse::success([
            'grupo' => array_merge((array) $grupo, [
                'tipo' => $validated['tipo'],
                'aula' => $grupo->aula,
            ]),
            'estudiantes' => $estudiantes,
        ]);
    }

    public function listarEstudiantesGrupo(Request $request, string $id)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'tipo' => ['required', 'in:regular,verano'],
        ]);

        $groupSession = DB::table('group_sessions')
            ->where('group_id', $id)
            ->orderByDesc('start_date')
            ->first();

        if (!$groupSession) {
            return ApiResponse::notFound('Grupo no encontrado.');
        }

        $estudiantes = $this->listarEstudiantesGrupoInterno($groupSession->id, $validated['tipo']);

        return ApiResponse::success($estudiantes);
    }

    public function listarEstudiantesDisponibles(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'tipo' => ['required', 'in:regular,verano'],
            'nivel' => ['required', 'string', 'max:10'],
            'id_grupo' => ['nullable', 'string', 'max:10'],
        ]);

        $groupSessionId = null;
        if (!empty($validated['id_grupo'])) {
            $groupSessionId = DB::table('group_sessions')
                ->where('group_id', $validated['id_grupo'])
                ->orderByDesc('start_date')
                ->value('id');
        }

        if ($validated['tipo'] === 'verano') {
            $query = DB::table('students as s')
                ->join('people as p', 'p.id', '=', 's.person_id')
                ->select(
                    's.id as id_estudiante',
                    DB::raw("concat(p.first_name, ' ', p.last_name) as estudiante"),
                    's.level as nivel',
                    's.status as estado'
                )
                ->where('s.type', 'verano')
                ->where('s.level', $validated['nivel'])
                ->whereNotIn('s.status', ['En proceso', 'En prueba']);

            if ($groupSessionId) {
                $query->leftJoin('enrollments as e', function ($join) use ($groupSessionId) {
                    $join->on('e.student_id', '=', 's.id')
                        ->where('e.group_session_id', '=', $groupSessionId);
                })->whereNull('e.student_id');
            }

            return ApiResponse::success($query->orderBy('p.first_name')->orderBy('p.last_name')->get());
        }

        $saldos = DB::table('balance_movements')
            ->select(
                'student_id',
                DB::raw("sum(case when movement_type in ('charge','adjustment') then amount else 0 end) - sum(case when movement_type = 'payment' then amount else 0 end) as saldo")
            )
            ->groupBy('student_id');

        $query = DB::table('students as s')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->leftJoinSub($saldos, 'saldo', function ($join) {
                $join->on('saldo.student_id', '=', 's.id');
            })
            ->select(
                's.id as id_estudiante',
                DB::raw("concat(p.first_name, ' ', p.last_name) as estudiante"),
                's.level as nivel',
                's.status as estado',
                DB::raw("case when s.is_utp = 1 then 'SI' else 'NO' end as es_estudiante"),
                DB::raw('coalesce(saldo.saldo, 0) as saldo_pendiente')
            )
            ->where('s.type', 'regular')
            ->where('s.level', $validated['nivel'])
            ->whereNotIn('s.status', ['En proceso', 'En prueba']);

        if ($groupSessionId) {
            $query->leftJoin('enrollments as e', function ($join) use ($groupSessionId) {
                $join->on('e.student_id', '=', 's.id')
                    ->where('e.group_session_id', '=', $groupSessionId);
            })->whereNull('e.student_id');
        }

        return ApiResponse::success($query->orderBy('p.first_name')->orderBy('p.last_name')->get());
    }

    private function listarEstudiantesGrupoInterno(int $idSesion, string $tipo)
    {
        if ($tipo === 'verano') {
            return DB::table('enrollments as e')
                ->join('group_sessions as gs', 'gs.id', '=', 'e.group_session_id')
                ->join('students as s', 's.id', '=', 'e.student_id')
                ->join('people as p', 'p.id', '=', 's.person_id')
                ->where('e.group_session_id', $idSesion)
                ->where('s.type', 'verano')
                ->select(
                    's.id as id_estudiante',
                    DB::raw("concat(p.first_name, ' ', p.last_name) as estudiante"),
                    's.status as estado',
                    'gs.classroom as aula',
                    'e.final_grade as nota_final'
                )
                ->orderBy('p.first_name')
                ->orderBy('p.last_name')
                ->get();
        }

        $saldos = DB::table('balance_movements')
            ->select(
                'student_id',
                DB::raw("sum(case when movement_type in ('charge','adjustment') then amount else 0 end) - sum(case when movement_type = 'payment' then amount else 0 end) as saldo")
            )
            ->groupBy('student_id');

        return DB::table('enrollments as e')
            ->join('group_sessions as gs', 'gs.id', '=', 'e.group_session_id')
            ->join('students as s', 's.id', '=', 'e.student_id')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->leftJoinSub($saldos, 'saldo', function ($join) {
                $join->on('saldo.student_id', '=', 's.id');
            })
            ->where('e.group_session_id', $idSesion)
            ->where('s.type', 'regular')
            ->select(
                's.id as id_estudiante',
                DB::raw("concat(p.first_name, ' ', p.last_name) as estudiante"),
                's.status as estado',
                's.level as nivel',
                DB::raw("case when s.is_utp = 1 then 'SI' else 'NO' end as es_estudiante"),
                DB::raw('coalesce(saldo.saldo, 0) as saldo_pendiente'),
                'gs.classroom as aula',
                'e.final_grade as nota_final'
            )
            ->orderBy('p.first_name')
            ->orderBy('p.last_name')
            ->get();
    }

    private function generarIdGrupo(): string
    {
        do {
            $id = 'GRP' . Str::upper(Str::random(7));
            $existe = DB::table('groups')->where('id', $id)->exists();
        } while ($existe);

        return $id;
    }
}
