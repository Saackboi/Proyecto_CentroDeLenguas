<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportService
{
    public function datatableResponse(Request $request, $query, array $searchColumns, array $orderableMap, array $defaultOrder = []): \Illuminate\Http\JsonResponse
    {
        if (!$request->has('draw')) {
            return response()->json($query->get());
        }

        $draw = (int) $request->input('draw');
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value'));

        $totalQuery = clone $query;
        $recordsTotal = $totalQuery->count();

        if ($search !== '') {
            $query->where(function ($builder) use ($searchColumns, $search) {
                foreach ($searchColumns as $column) {
                    $builder->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        $filteredQuery = clone $query;
        $recordsFiltered = $filteredQuery->count();

        $orderIndex = $request->input('order.0.column');
        $orderDir = strtolower((string) $request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $columns = $request->input('columns', []);

        if ($orderIndex !== null && isset($columns[$orderIndex]['data'])) {
            $dataKey = $columns[$orderIndex]['data'];
            if (isset($orderableMap[$dataKey])) {
                $query->orderBy($orderableMap[$dataKey], $orderDir);
            }
        } elseif (!empty($defaultOrder)) {
            $query->orderBy($defaultOrder['column'], $defaultOrder['dir'] ?? 'asc');
        }

        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $query->get(),
        ]);
    }

    public function configurarReporte(Request $request): array
    {
        $tipo = (string) $request->input('tipo');

        switch ($tipo) {
            case 'nivelEstudiante':
                $nivel = (string) $request->input('nivel');
                $verano = $this->parseBoolean($request->input('verano'));
                if ($nivel === '') {
                    return ['error' => 'Debe indicar el nivel.'];
                }

                $query = DB::table('students as s')
                    ->join('people as p', 'p.id', '=', 's.person_id')
                    ->select(
                        's.id as id_estudiante',
                        DB::raw("concat(p.first_name, ' ', p.last_name) as estudiante"),
                        's.level as nivel'
                    )
                    ->where('s.level', $nivel)
                    ->where('s.type', $verano ? 'verano' : 'regular');

                $headers = ['N', 'Estudiante', 'Cedula', 'Nivel'];
                $orderMap = [
                    'id_estudiante' => 'id_estudiante',
                    'estudiante' => 'estudiante',
                    'nivel' => 'nivel',
                ];

                return [
                    'title' => 'Estudiantes por nivel',
                    'headers' => $headers,
                    'columns' => ['estudiante', 'id_estudiante', 'nivel'],
                    'query' => DB::query()->fromSub($query, 't'),
                    'search' => ['id_estudiante', 'estudiante', 'nivel'],
                    'order' => $orderMap,
                    'defaultOrder' => ['column' => 'estudiante', 'dir' => 'asc'],
                ];

            case 'statusEstudiante':
                $estado = (string) $request->input('estado');
                $verano = $this->parseBoolean($request->input('verano'));
                if ($estado === '') {
                    return ['error' => 'Debe indicar el status.'];
                }

                $query = DB::table('students as s')
                    ->join('people as p', 'p.id', '=', 's.person_id')
                    ->select(
                        's.id as id_estudiante',
                        DB::raw("concat(p.first_name, ' ', p.last_name) as estudiante"),
                        's.status as estado'
                    )
                    ->where('s.status', $estado)
                    ->where('s.type', $verano ? 'verano' : 'regular');

                $headers = ['N', 'Estudiante', 'Cedula', 'Status'];
                $orderMap = [
                    'id_estudiante' => 'id_estudiante',
                    'estudiante' => 'estudiante',
                    'estado' => 'estado',
                ];

                return [
                    'title' => 'Estudiantes por status',
                    'headers' => $headers,
                    'columns' => ['estudiante', 'id_estudiante', 'estado'],
                    'query' => DB::query()->fromSub($query, 't'),
                    'search' => ['id_estudiante', 'estudiante', 'estado'],
                    'order' => $orderMap,
                    'defaultOrder' => ['column' => 'estudiante', 'dir' => 'asc'],
                ];

            case 'saldoEstudiante':
                $saldoCancelado = $this->parseBoolean($request->input('saldo_cancelado'));

                $saldos = DB::table('balance_movements')
                    ->select(
                        'student_id',
                        DB::raw("SUM(CASE WHEN movement_type IN ('charge','adjustment') THEN amount ELSE 0 END) - SUM(CASE WHEN movement_type = 'payment' THEN amount ELSE 0 END) as saldo_valor")
                    )
                    ->groupBy('student_id');

                $base = DB::table('students as s')
                    ->join('people as p', 'p.id', '=', 's.person_id')
                    ->leftJoinSub($saldos, 'b', function ($join) {
                        $join->on('b.student_id', '=', 's.id');
                    })
                    ->select(
                        's.id as id_estudiante',
                        DB::raw("concat(p.first_name, ' ', p.last_name) as estudiante"),
                        DB::raw('coalesce(b.saldo_valor, 0) as saldo_valor'),
                        DB::raw("case when coalesce(b.saldo_valor, 0) <= 0 then 'Saldo cancelado' else concat('B/.', FORMAT(coalesce(b.saldo_valor,0),2)) end as saldo")
                    );

                if ($saldoCancelado) {
                    $base->whereRaw('coalesce(b.saldo_valor, 0) <= 0');
                } else {
                    $base->whereRaw('coalesce(b.saldo_valor, 0) > 0');
                }

                $headers = ['N', 'Estudiante', 'Cedula', 'Saldo'];
                $orderMap = [
                    'id_estudiante' => 'id_estudiante',
                    'estudiante' => 'estudiante',
                    'saldo' => 'saldo_valor',
                ];

                return [
                    'title' => 'Estudiantes por saldo',
                    'headers' => $headers,
                    'columns' => ['estudiante', 'id_estudiante', 'saldo'],
                    'query' => DB::query()->fromSub($base, 't'),
                    'search' => ['id_estudiante', 'estudiante', 'saldo'],
                    'order' => $orderMap,
                    'defaultOrder' => ['column' => 'estudiante', 'dir' => 'asc'],
                    'footer' => $saldoCancelado ? null : 'total_saldo',
                ];

            case 'nivelProfesor':
                $nivel = (string) $request->input('nivel');
                if ($nivel === '') {
                    return ['error' => 'Debe indicar el nivel.'];
                }

                $query = DB::table('group_sessions as gs')
                    ->join('groups as g', 'g.id', '=', 'gs.group_id')
                    ->join('teachers as t', 't.id', '=', 'gs.teacher_id')
                    ->join('people as p', 'p.id', '=', 't.person_id')
                    ->select(
                        't.id as id_profesor',
                        DB::raw("concat(p.first_name, ' ', p.last_name) as profesor"),
              
                        'g.id as id_grupo',
                        'g.level as nivel',
                        'gs.classroom as aula',
                        'gs.start_date as inicio',
                        'gs.end_date as fin'
                    )
                    ->where('g.level', $nivel);

                $headers = ['N', 'Profesor', 'Cedula', 'Grupo', 'Aula', 'Inicio', 'Fin'];
                $orderMap = [
                    'id_profesor' => 'id_profesor',
                    'profesor' => 'profesor',
                    'id_grupo' => 'id_grupo',
                    'aula' => 'aula',
                ];

                return [
                    'title' => 'Profesores por nivel',
                    'headers' => $headers,
                    'columns' => ['profesor', 'id_profesor', 'id_grupo', 'aula', 'inicio', 'fin'],
                    'query' => DB::query()->fromSub($query, 't'),
                    'search' => ['id_profesor', 'profesor', 'id_grupo', 'aula'],
                    'order' => $orderMap,
                    'defaultOrder' => ['column' => 'profesor', 'dir' => 'asc'],
                ];

            default:
                return ['error' => 'Tipo de reporte no soportado.'];
        }
    }

    /**
     * Parse various boolean-like values from request input.
     */
    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_null($value)) {
            return false;
        }
        $v = strtolower((string) $value);
        return in_array($v, ['1', 'true', 'on', 'yes'], true);
    }
}
