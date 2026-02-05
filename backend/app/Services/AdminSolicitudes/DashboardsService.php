<?php

// Servicio para la gestión de dashboards de solicitudes administrativas
// Proporciona datos tabulares sobre estudiantes, profesores y grupos.

namespace App\Services\AdminSolicitudes;

use App\Services\AdminReportService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardsService
{
    use AdminSolicitudesHelpers;

    public function __construct(private AdminReportService $reportService)
    {
    }

    public function dashboardEstudiantes(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $query = DB::query()->fromSub($this->getBaseEstudiantes(), 't');

        return $this->reportService->datatableResponse(
            $request,
            $query,
            ['id_estudiante', 'estudiante', 'nivel', 'profesor', 'estado', 'tipo'],
            [
                'id_estudiante' => 'id_estudiante',
                'estudiante' => 'estudiante',
                'nivel' => 'nivel',
                'profesor' => 'profesor',
                'estado' => 'estado',
                'tipo' => 'tipo',
            ],
            ['column' => 'estudiante', 'dir' => 'asc']
        );
    }

    public function dashboardProfesores(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }
        $query = DB::query()->fromSub($this->getBaseProfesores(), 't');

        return $this->reportService->datatableResponse(
            $request,
            $query,
            ['id_profesor', 'profesor', 'nivel', 'aula', 'verano'],
            [
                'id_profesor' => 'id_profesor',
                'profesor' => 'profesor',
                'nivel' => 'nivel',
                'aula' => 'aula',
                'verano' => 'verano',
            ],
            ['column' => 'profesor', 'dir' => 'asc']
        );
    }

    public function dashboardGrupos(Request $request)
    {
        return $this->reportService->datatableResponse(
            $request,
            DB::query()->fromSub($this->getBaseGrupos(), 'g'),
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

    public function dashboardResumen()
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $counts = [
            'estudiantes' => DB::query()->fromSub($this->getBaseEstudiantes(), 't')->count(),
            'profesores' => DB::query()->fromSub($this->getBaseProfesores(), 't')->count(),
            'grupos' => DB::query()->fromSub($this->getBaseGrupos(), 'g')->count(),
            'solicitudes' => $this->contarSolicitudes()
        ];

        return ApiResponse::success($counts);
    }

    private function getBaseEstudiantes()
    {
        $ultimos = DB::table('enrollments as e')
            ->join('group_sessions as gs', 'gs.id', '=', 'e.group_session_id')
            ->select('e.student_id', DB::raw('max(gs.start_date) as max_fecha'))
            ->groupBy('e.student_id');

        $ultGrupo = DB::table('enrollments as e1')
            ->join('group_sessions as gs1', 'gs1.id', '=', 'e1.group_session_id')
            ->joinSub($ultimos, 'ult', function ($join) {
                $join->on('e1.student_id', '=', 'ult.student_id')
                    ->on('gs1.start_date', '=', 'ult.max_fecha');
            })
            ->select('e1.student_id', 'e1.group_session_id');

        return DB::table('students as s')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->leftJoinSub($ultGrupo, 'ult_grupo', function ($join) {
                $join->on('s.id', '=', 'ult_grupo.student_id');
            })
            ->leftJoin('group_sessions as gs', 'gs.id', '=', 'ult_grupo.group_session_id')
            ->leftJoin('teachers as t', 't.id', '=', 'gs.teacher_id')
            ->leftJoin('people as pt', 'pt.id', '=', 't.person_id')
            ->whereNotIn('s.status', ['En proceso', 'En prueba'])
            ->select(
                's.id as id_estudiante',
                DB::raw("concat(p.first_name, ' ', p.last_name) as estudiante"),
                DB::raw("coalesce(s.level, '--') as nivel"),
                DB::raw("ifnull(concat(pt.first_name, ' ', pt.last_name), '--') as profesor"),
                's.status as estado',
                's.type as tipo'
            );
    }

    private function getBaseProfesores()
    {
        $grupoActual = DB::table('group_sessions as gs')
            ->join('groups as g', 'g.id', '=', 'gs.group_id')
            ->whereNotNull('g.level')
            ->whereRaw('DATE_ADD(gs.end_date, INTERVAL 5 DAY) > CURDATE()')
            ->select('gs.teacher_id', 'gs.group_id', 'gs.start_date', 'gs.end_date', 'gs.id as group_session_id')
            ->whereRaw('gs.start_date = (
                SELECT MAX(gs2.start_date)
                FROM group_sessions gs2
                JOIN groups g2 ON g2.id = gs2.group_id
                WHERE gs2.teacher_id = gs.teacher_id AND g2.level IS NOT NULL
            )');

        $tipo = DB::table('enrollments as e')
            ->join('students as s', 's.id', '=', 'e.student_id')
            ->select('e.group_session_id', DB::raw("max(case when s.type = 'verano' then 1 else 0 end) as has_verano"))
            ->groupBy('e.group_session_id');

        return DB::table('teachers as t')
            ->join('people as p', 'p.id', '=', 't.person_id')
            ->leftJoinSub($grupoActual, 'grupo_actual', function ($join) {
                $join->on('grupo_actual.teacher_id', '=', 't.id');
            })
            ->leftJoin('groups as g', 'g.id', '=', 'grupo_actual.group_id')
            ->leftJoin('group_sessions as gs', 'gs.id', '=', 'grupo_actual.group_session_id')
            ->leftJoinSub($tipo, 'tipo', function ($join) {
                $join->on('tipo.group_session_id', '=', 'grupo_actual.group_session_id');
            })
            ->select(
                't.id as id_profesor',
                DB::raw("concat(p.first_name, ' ', p.last_name) as profesor"),
                DB::raw("coalesce(g.level, '--') as nivel"),
                DB::raw("coalesce(gs.classroom, '--') as aula"),
                DB::raw("case when coalesce(tipo.has_verano, 0) = 1 then true else false end as verano")
            );
    }

    private function getBaseGrupos()
    {
        return DB::table('group_sessions as gs')
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
    }

    private function contarSolicitudes(): int
    {
        $ubicacion = DB::table('students')
            ->where('type', 'regular')
            ->whereIn('status', ['En proceso', 'En prueba'])
            ->count();

        $verano = DB::table('students')
            ->where('type', 'verano')
            ->where('status', 'En proceso')
            ->count();

        $abonos = DB::table('payments')
            ->where('payment_type', 'Abono')
            ->where('status', 'Pendiente')
            ->count();

        return $ubicacion + $verano + $abonos;
    }
}
