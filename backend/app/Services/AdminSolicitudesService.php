<?php

namespace App\Services;

use App\Mail\EstudianteCredencialesMail;
use App\Mail\EstudianteResetPasswordMail;
use App\Services\AdminReportService;
use App\Services\SaldoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminSolicitudesService
{
    public function __construct(
        private SaldoService $saldoService,
        private AdminReportService $reportService
    ) {
    }


    private function asegurarAdmin()
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->role !== 'Admin') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return null;
    }

    private function generarIdGrupo(): string
    {
        do {
            $id = 'GRP' . Str::upper(Str::random(7));
            $existe = DB::table('groups')->where('id', $id)->exists();
        } while ($existe);

        return $id;
    }


    private function crearNotificacion(string $idEstudiante, string $titulo, string $cuerpo, string $tipo = 'estado'): void
    {
        DB::table('notifications')->insert([
            'student_id' => $idEstudiante,
            'title' => $titulo,
            'body' => $cuerpo,
            'type' => $tipo,
            'read_at' => null,
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

        $existe = DB::table('users')
            ->where('email', $correo)
            ->exists();

        if ($existe) {
            return null;
        }

        $passwordPlano = Str::random(10);

        DB::table('users')->insert([
            'email' => $correo,
            'password' => Hash::make($passwordPlano),
            'role' => 'Estudiante',
            'token_recuperacion' => null,
            'expiracion_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $passwordPlano;
    }

    public function crearProfesor(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
            'apellido' => ['required', 'string', 'max:50'],
            'correo' => ['required', 'email', 'max:100'],
            'id_idioma' => ['required', 'string', 'max:15', 'exists:languages,id'],
            'estado' => ['nullable', 'in:Activo,Inactivo'],
        ]);

        $correo = strtolower(trim($validated['correo']));
        $estado = $validated['estado'] ?? 'Activo';

        if (DB::table('users')->where('email', $correo)->exists()) {
            return response()->json(['message' => 'El correo ya esta registrado.'], 409);
        }

        DB::beginTransaction();
        $recuperacionEnviada = false;
        $teacherId = null;

        try {
            $passwordPlano = Str::random(10);
            $personId = DB::table('people')->insertGetId([
                'first_name' => $validated['nombre'],
                'last_name' => $validated['apellido'],
                'phone' => null,
                'email_personal' => $correo,
                'email_institucional' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')->insert([
                'email' => $correo,
                'password' => Hash::make($passwordPlano),
                'role' => 'Profesor',
                'token_recuperacion' => null,
                'expiracion_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $teacherId = DB::table('teachers')->insertGetId([
                'person_id' => $personId,
                'language_id' => $validated['id_idioma'],
                'status' => $estado,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            try {
                $token = Str::random(64);
                $expira = now()->addDay();

                DB::table('users')
                    ->where('email', $correo)
                    ->update([
                        'token_recuperacion' => $token,
                        'expiracion_token' => $expira,
                    ]);

                $link = rtrim(config('app.url'), '/') . '/profesores/reset?token=' . $token;

                Mail::to($correo)->send(new EstudianteResetPasswordMail($correo, $link, 'profesor', 'profesores'));
                $recuperacionEnviada = true;
            } catch (\Throwable $e) {
                $recuperacionEnviada = false;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear el profesor. Intente nuevamente.',
            ], 500);
        }

        return response()->json([
            'message' => 'Profesor creado.',
            'id_profesor' => $teacherId,
            'correo' => $correo,
            'recuperacion_enviada' => $recuperacionEnviada,
        ], 201);
    }

    public function actualizarProfesor(Request $request, string $id)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
            'apellido' => ['required', 'string', 'max:50'],
            'correo' => ['required', 'email', 'max:100'],
            'id_idioma' => ['required', 'string', 'max:15', 'exists:languages,id'],
            'estado' => ['required', 'in:Activo,Inactivo'],
        ]);

        $profesor = DB::table('teachers as t')
            ->join('people as p', 'p.id', '=', 't.person_id')
            ->select('t.id', 't.person_id', 'p.email_personal')
            ->where('t.id', $id)
            ->first();

        if (!$profesor) {
            return response()->json(['message' => 'Profesor no encontrado.'], 404);
        }

        $nuevoCorreo = strtolower(trim($validated['correo']));
        $correoActual = strtolower(trim($profesor->email_personal ?? ''));

        if ($nuevoCorreo !== $correoActual) {
            $correoEnUso = DB::table('users')
                ->where('email', $nuevoCorreo)
                ->exists();
            if ($correoEnUso) {
                return response()->json(['message' => 'El correo ya esta en uso.'], 409);
            }
        }

        DB::beginTransaction();

        try {
            DB::table('teachers')
                ->where('id', $id)
                ->update([
                    'language_id' => $validated['id_idioma'],
                    'status' => $validated['estado'],
                    'updated_at' => now(),
                ]);

            DB::table('people')
                ->where('id', $profesor->person_id)
                ->update([
                    'first_name' => $validated['nombre'],
                    'last_name' => $validated['apellido'],
                    'email_personal' => $nuevoCorreo,
                    'updated_at' => now(),
                ]);

            if ($nuevoCorreo !== $correoActual) {
                DB::table('users')
                    ->where('email', $correoActual)
                    ->update(['email' => $nuevoCorreo]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar el profesor. Intente nuevamente.',
            ], 500);
        }

        return response()->json(['message' => 'Profesor actualizado.']);
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
            return response()->json([
                'message' => 'Hay estudiantes que no existen en el sistema.',
            ], 422);
        }

        $profesor = DB::table('teachers')
            ->select('language_id')
            ->where('id', $validated['id_profesor'])
            ->first();

        if (!$profesor) {
            return response()->json(['message' => 'Profesor no encontrado.'], 404);
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

            return response()->json([
                'message' => 'Error al crear el grupo. Intente nuevamente.',
            ], 500);
        }

        return response()->json([
            'message' => 'Grupo creado.',
            'id_grupo' => $idGrupo,
        ], 201);
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
            return response()->json(['message' => 'Grupo no encontrado.'], 404);
        }

        $groupSession = DB::table('group_sessions')
            ->where('group_id', $id)
            ->orderByDesc('start_date')
            ->first();

        if (!$groupSession) {
            return response()->json(['message' => 'Grupo no encontrado.'], 404);
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
                return response()->json([
                    'message' => 'Hay estudiantes para agregar que no existen en el sistema.',
                ], 422);
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

            return response()->json([
                'message' => 'Error al actualizar el grupo. Intente nuevamente.',
            ], 500);
        }

        return response()->json([
            'message' => 'Grupo actualizado.',
            'agregados' => count($idsAgregar),
            'eliminados' => count($idsEliminar),
            'ajustes_pendientes' => $ajustesPendientes,
        ]);
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
            return response()->json(['message' => 'Los ajustes aplican solo a estudiantes regulares.'], 422);
        }

        $existeGrupo = DB::table('groups')->where('id', $id)->exists();
        if (!$existeGrupo) {
            return response()->json(['message' => 'Grupo no encontrado.'], 404);
        }

        $groupSession = DB::table('group_sessions')
            ->where('group_id', $id)
            ->orderByDesc('start_date')
            ->first();

        if (!$groupSession) {
            return response()->json(['message' => 'Grupo no encontrado.'], 404);
        }

        $enGrupo = DB::table('enrollments')
            ->where('group_session_id', $groupSession->id)
            ->where('student_id', $validated['id_estudiante'])
            ->exists();

        if (!$enGrupo) {
            return response()->json(['message' => 'Estudiante no pertenece al grupo.'], 404);
        }

        $monto = $this->saldoService->obtenerMontoMatricula($validated['id_estudiante']);
        $saldoActual = $this->saldoService->calcularSaldo($validated['id_estudiante']);
        $saldoNuevo = max(0.00, $saldoActual - $monto);

        return response()->json([
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
            return response()->json(['message' => 'Los ajustes aplican solo a estudiantes regulares.'], 422);
        }

        $groupSession = DB::table('group_sessions')
            ->where('group_id', $id)
            ->orderByDesc('start_date')
            ->first();

        if (!$groupSession) {
            return response()->json(['message' => 'Grupo no encontrado.'], 404);
        }

        $enGrupo = DB::table('enrollments')
            ->where('group_session_id', $groupSession->id)
            ->where('student_id', $validated['id_estudiante'])
            ->exists();

        if ($enGrupo) {
            return response()->json([
                'message' => 'Debe retirar al estudiante del grupo antes de aplicar el ajuste.',
            ], 409);
        }

        $yaAjustado = DB::table('balance_movements')
            ->where('student_id', $validated['id_estudiante'])
            ->where('group_session_id', $groupSession->id)
            ->where('movement_type', 'adjustment')
            ->where('reason', 'retiro')
            ->exists();

        if ($yaAjustado) {
            return response()->json([
                'message' => 'El ajuste de retiro ya fue aplicado.',
            ], 409);
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

            return response()->json([
                'message' => 'Error al aplicar el ajuste. Intente nuevamente.',
            ], 500);
        }

        return response()->json([
            'message' => 'Ajuste aplicado.',
            'monto' => $montoAjuste,
        ]);
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
            return response()->json(['message' => 'Grupo no encontrado.'], 404);
        }

        $estudiantes = $this->listarEstudiantesGrupoInterno($grupo->id_sesion, $validated['tipo']);

        return response()->json([
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
            return response()->json(['message' => 'Grupo no encontrado.'], 404);
        }

        $estudiantes = $this->listarEstudiantesGrupoInterno($groupSession->id, $validated['tipo']);

        return response()->json($estudiantes);
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

            return response()->json($query->orderBy('p.first_name')->orderBy('p.last_name')->get());
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

        return response()->json($query->orderBy('p.first_name')->orderBy('p.last_name')->get());
    }

    public function detalleEstudiante(Request $request, string $id)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'tipo' => ['required', 'in:regular,verano'],
        ]);

        if ($validated['tipo'] === 'verano') {
            $datos = DB::table('students as s')
                ->join('people as p', 'p.id', '=', 's.person_id')
                ->leftJoin('student_profiles as sp', 'sp.student_id', '=', 's.id')
                ->leftJoin('guardians as g', 'g.student_id', '=', 's.id')
                ->leftJoin('student_contacts as sc', 'sc.student_id', '=', 's.id')
                ->where('s.id', $id)
                ->where('s.type', 'verano')
                ->select(
                    's.id as id_estudiante',
                    DB::raw("concat(p.first_name, ' ', p.last_name) as nombre_completo"),
                    's.status as estado',
                    's.level as nivel',
                    'p.phone as celular',
                    'sp.birth_date as fecha_nacimiento',
                    'sp.home_number as numero_casa',
                    'sp.address as domicilio',
                    'sp.gender as sexo',
                    'p.email_personal as correo',
                    'sp.school as colegio',
                    'sc.allergies as alergias',
                    'sc.blood_type as tipo_sangre',
                    'sc.emergency_name as contacto_nombre',
                    'sc.emergency_phone as contacto_telefono',
                    'g.father_name as nombre_padre',
                    'g.father_workplace as lugar_trabajo_padre',
                    'g.father_work_phone as telefono_trabajo_padre',
                    'g.father_phone as celular_padre',
                    'g.mother_name as nombre_madre',
                    'g.mother_workplace as lugar_trabajo_madre',
                    'g.mother_work_phone as telefono_trabajo_madre',
                    'g.mother_phone as celular_madre'
                )
                ->first();

            if (!$datos) {
                return response()->json(['message' => 'Estudiante no encontrado.'], 404);
            }

            return response()->json(['tipo' => 'verano', 'data' => $datos]);
        }

        $datos = DB::table('students as s')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->where('s.id', $id)
            ->where('s.type', 'regular')
            ->select(
                's.id as id_estudiante',
                DB::raw('null as tipo_id'),
                'p.first_name as nombre',
                'p.last_name as apellido',
                'p.email_personal as correo_personal',
                'p.email_institucional as correo_utp',
                'p.phone as telefono',
                's.level as nivel',
                's.status as estado',
                DB::raw("case when s.is_utp = 1 then 'SI' else 'NO' end as es_estudiante"),
                DB::raw('null as deuda_total')
            )
            ->first();

        if (!$datos) {
            return response()->json(['message' => 'Estudiante no encontrado.'], 404);
        }

        $saldo = $this->saldoService->calcularSaldo($id);

        return response()->json([
            'tipo' => 'regular',
            'data' => array_merge((array) $datos, [
                'saldo_pendiente' => $saldo,
                'deuda_total' => $saldo,
            ]),
        ]);
    }

    public function detalleProfesor(string $id)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $profesor = DB::table('teachers as t')
            ->join('people as p', 'p.id', '=', 't.person_id')
            ->leftJoin('languages as l', 'l.id', '=', 't.language_id')
            ->where('t.id', $id)
            ->select(
                't.id as id_profesor',
                'p.first_name as nombre',
                'p.last_name as apellido',
                'p.email_personal as correo',
                't.status as estado',
                't.language_id as id_idioma',
                'l.name as idioma'
            )
            ->first();

        if (!$profesor) {
            return response()->json(['message' => 'Profesor no encontrado.'], 404);
        }

        return response()->json($profesor);
    }

    public function dashboardEstudiantes(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

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

        $base = DB::table('students as s')
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

        $query = DB::query()->fromSub($base, 't');

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

        $base = DB::table('teachers as t')
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

        $query = DB::query()->fromSub($base, 't');

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
        return $this->listarGrupos($request);
    }


    public function reportes(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $config = $this->reportService->configurarReporte($request);
        if (isset($config['error'])) {
            return response()->json(['message' => $config['error']], 422);
        }

        return $this->reportService->datatableResponse(
            $request,
            $config['query'],
            $config['search'],
            $config['order'],
            $config['defaultOrder'] ?? []
        );
    }

    public function exportarReportePdf(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $config = $this->reportService->configurarReporte($request);
        if (isset($config['error'])) {
            return response()->json(['message' => $config['error']], 422);
        }

        $rows = $config['query']->get();
        $headers = $config['headers'];
        $title = $config['title'];
        $columns = $config['columns'];

        $footer = null;
        if (($config['footer'] ?? null) === 'total_saldo') {
            $totalSaldo = $rows->sum(function ($row) {
                return (float) ($row->saldo_valor ?? 0);
            });
            $footer = 'Total saldo: B/.' . number_format($totalSaldo, 2, '.', '');
        }

        $pdf = Pdf::loadView('reports.reporte', [
            'title' => $title,
            'headers' => $headers,
            'columns' => $columns,
            'rows' => $rows,
            'footer' => $footer,
        ]);

        $filename = 'reporte_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    public function actualizarEstudiante(Request $request, string $id)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'tipo_id' => ['required', 'string', 'max:15'],
            'nombre' => ['required', 'string', 'max:50'],
            'apellido' => ['required', 'string', 'max:50'],
            'correo_personal' => ['required', 'email', 'max:100'],
            'correo_utp' => ['nullable', 'email', 'max:100'],
            'telefono' => ['required', 'string', 'max:20'],
            'nivel' => ['nullable', 'string', 'max:10'],
            'estado' => ['required', 'in:Activo,Inactivo,En proceso,En prueba'],
            'es_estudiante' => ['required', 'in:SI,NO'],
            'saldo_pendiente' => ['nullable', 'numeric'],
        ]);

        $estudiante = DB::table('students as s')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->where('s.id', $id)
            ->where('s.type', 'regular')
            ->select('s.id', 's.person_id', 's.status', 'p.email_personal', 'p.email_institucional')
            ->first();

        if (!$estudiante) {
            return response()->json(['message' => 'Estudiante no encontrado.'], 404);
        }

        $correoPersonal = strtolower(trim($validated['correo_personal']));
        $correoUtp = $validated['correo_utp'] ? strtolower(trim($validated['correo_utp'])) : null;
        $isUtp = $validated['es_estudiante'] === 'SI';

        DB::beginTransaction();

        try {
            DB::table('people')
                ->where('id', $estudiante->person_id)
                ->update([
                    'first_name' => $validated['nombre'],
                    'last_name' => $validated['apellido'],
                    'email_personal' => $correoPersonal,
                    'email_institucional' => $correoUtp,
                    'phone' => $validated['telefono'],
                    'updated_at' => now(),
                ]);

            DB::table('students')
                ->where('id', $id)
                ->update([
                    'level' => $validated['nivel'],
                    'status' => $validated['estado'],
                    'is_utp' => $isUtp,
                    'updated_at' => now(),
                ]);

            $correoCuenta = $correoUtp ?: $correoPersonal;
            $correoCuentaActual = strtolower(trim($estudiante->email_institucional ?? $estudiante->email_personal ?? ''));
            if ($correoCuentaActual !== '' && $correoCuenta !== $correoCuentaActual) {
                $existeCorreo = DB::table('users')
                    ->where('email', $correoCuenta)
                    ->exists();

                if ($existeCorreo) {
                    DB::rollBack();
                    return response()->json(['message' => 'El correo ya esta en uso.'], 409);
                }

                DB::table('users')
                    ->where('email', $correoCuentaActual)
                    ->update(['email' => $correoCuenta]);
            }

            $saldoObjetivo = $validated['saldo_pendiente'];
            if ($validated['estado'] === 'Inactivo') {
                $saldoObjetivo = 0.00;
            }

            if ($saldoObjetivo !== null) {
                $saldoActual = $this->saldoService->calcularSaldo($id);
                $diferencia = round((float) $saldoObjetivo - $saldoActual, 2);

                if (abs($diferencia) >= 0.01) {
                    $this->saldoService->crearMovimientoSaldo(
                        $id,
                        'ajuste',
                        $diferencia,
                        'ajuste_admin',
                        null,
                        null
                    );
                }

                $this->saldoService->actualizarSaldoCache($id);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar el estudiante. Intente nuevamente.',
            ], 500);
        }

        return response()->json(['message' => 'Estudiante actualizado.']);
    }

    public function actualizarEstudianteVerano(Request $request, string $id)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'nivel' => ['nullable', 'string', 'max:10'],
            'estado' => ['required', 'in:Activo,Inactivo,En proceso'],
            'nombre_completo' => ['required', 'string', 'max:100'],
            'celular' => ['required', 'string', 'max:20'],
            'fecha_nacimiento' => ['required', 'date'],
            'numero_casa' => ['nullable', 'string', 'max:10'],
            'domicilio' => ['required', 'string', 'max:100'],
            'sexo' => ['required', 'in:Masculino,Femenino'],
            'correo' => ['required', 'email', 'max:100'],
            'colegio' => ['required', 'string', 'max:100'],
            'tipo_sangre' => ['required', 'string', 'max:45'],
            'alergias' => ['nullable', 'string', 'max:255'],
            'contacto_nombre' => ['nullable', 'string', 'max:100'],
            'contacto_telefono' => ['nullable', 'string', 'max:20'],
            'nombre_madre' => ['nullable', 'string', 'max:100'],
            'lugar_trabajo_madre' => ['nullable', 'string', 'max:100'],
            'telefono_trabajo_madre' => ['nullable', 'string', 'max:20'],
            'celular_madre' => ['nullable', 'string', 'max:20'],
            'nombre_padre' => ['nullable', 'string', 'max:100'],
            'lugar_trabajo_padre' => ['nullable', 'string', 'max:100'],
            'telefono_trabajo_padre' => ['nullable', 'string', 'max:20'],
            'celular_padre' => ['nullable', 'string', 'max:20'],
        ]);

        $estudiante = DB::table('students')
            ->where('id', $id)
            ->where('type', 'verano')
            ->select('id', 'person_id')
            ->first();

        if (!$estudiante) {
            return response()->json(['message' => 'Estudiante no encontrado.'], 404);
        }

        DB::beginTransaction();

        try {
            DB::table('people')
                ->where('id', $estudiante->person_id)
                ->update([
                    'first_name' => $validated['nombre_completo'],
                    'last_name' => '',
                    'phone' => $validated['celular'],
                    'email_personal' => strtolower(trim($validated['correo'])),
                    'email_institucional' => null,
                    'updated_at' => now(),
                ]);

            DB::table('students')
                ->where('id', $id)
                ->update([
                    'level' => $validated['nivel'],
                    'status' => $validated['estado'],
                    'updated_at' => now(),
                ]);

            DB::table('student_profiles')->updateOrInsert(
                ['student_id' => $id],
                [
                    'birth_date' => $validated['fecha_nacimiento'],
                    'home_number' => $validated['numero_casa'],
                    'address' => $validated['domicilio'],
                    'gender' => $validated['sexo'],
                    'school' => $validated['colegio'],
                    'updated_at' => now(),
                ]
            );

            DB::table('guardians')->updateOrInsert(
                ['student_id' => $id],
                [
                    'father_name' => $validated['nombre_padre'] ?? '',
                    'father_workplace' => $validated['lugar_trabajo_padre'] ?? '',
                    'father_work_phone' => $validated['telefono_trabajo_padre'] ?? '',
                    'father_phone' => $validated['celular_padre'] ?? '',
                    'mother_name' => $validated['nombre_madre'] ?? '',
                    'mother_workplace' => $validated['lugar_trabajo_madre'] ?? '',
                    'mother_work_phone' => $validated['telefono_trabajo_madre'] ?? '',
                    'mother_phone' => $validated['celular_madre'] ?? '',
                    'updated_at' => now(),
                ]
            );

            DB::table('student_contacts')->updateOrInsert(
                ['student_id' => $id],
                [
                    'allergies' => $validated['alergias'] ?? 'No',
                    'blood_type' => $validated['tipo_sangre'],
                    'emergency_name' => $validated['contacto_nombre'] ?? null,
                    'emergency_phone' => $validated['contacto_telefono'] ?? null,
                    'updated_at' => now(),
                ]
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar el estudiante. Intente nuevamente.',
            ], 500);
        }

        return response()->json(['message' => 'Estudiante actualizado.']);
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
            'saldo_pendiente' => ['nullable', 'numeric'],
            'es_estudiante' => ['required', 'in:SI,NO'],
        ]);

        $correoPersonal = strtolower(trim($validated['correo_personal']));
        $correoUtp = $validated['correo_utp'] ? strtolower(trim($validated['correo_utp'])) : null;
        $isUtp = $validated['es_estudiante'] === 'SI';

        DB::beginTransaction();
        $credencialesEnviadas = false;

        try {
            $estudiante = DB::table('students')
                ->where('id', $validated['id_estudiante'])
                ->where('type', 'regular')
                ->select('id', 'person_id')
                ->first();

            if (!$estudiante) {
                DB::rollBack();
                return response()->json(['message' => 'Estudiante no encontrado.'], 404);
            }

            DB::table('people')
                ->where('id', $estudiante->person_id)
                ->update([
                    'first_name' => $validated['nombre'],
                    'last_name' => $validated['apellido'],
                    'email_personal' => $correoPersonal,
                    'email_institucional' => $correoUtp,
                    'phone' => $validated['telefono'],
                    'updated_at' => now(),
                ]);

            DB::table('students')
                ->where('id', $validated['id_estudiante'])
                ->update([
                    'level' => $validated['nivel'],
                    'status' => $validated['estado'],
                    'is_utp' => $isUtp,
                    'updated_at' => now(),
                ]);

            DB::table('payments')
                ->where('student_id', $validated['id_estudiante'])
                ->where('payment_type', 'PruebaUbicacion')
                ->where('status', 'Pendiente')
                ->update([
                    'amount' => 10.00,
                    'status' => 'Aceptado',
                    'updated_at' => now(),
                ]);

            $correoCuenta = $correoUtp ?: $correoPersonal;
            $passwordTemporal = $this->crearCuentaEstudiante($validated['id_estudiante'], $correoCuenta);
            if ($passwordTemporal) {
                try {
                    Mail::to($correoCuenta)->send(new EstudianteCredencialesMail($correoCuenta, $passwordTemporal));
                    $credencialesEnviadas = true;
                } catch (\Throwable $e) {
                    $credencialesEnviadas = false;
                }
            }

            $saldoObjetivo = $validated['saldo_pendiente'];
            if ($validated['estado'] === 'Inactivo') {
                $saldoObjetivo = 0.00;
            }

            if ($saldoObjetivo !== null) {
                $saldoActual = $this->saldoService->calcularSaldo($validated['id_estudiante']);
                $diferencia = round((float) $saldoObjetivo - $saldoActual, 2);

                if (abs($diferencia) >= 0.01) {
                    $this->saldoService->crearMovimientoSaldo(
                        $validated['id_estudiante'],
                        'ajuste',
                        $diferencia,
                        'ajuste_admin',
                        null,
                        null
                    );
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

        $registros = DB::table('students as s')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->leftJoin('payments as pay', function ($join) {
                $join->on('s.id', '=', 'pay.student_id')
                    ->where('pay.payment_type', '=', 'PruebaUbicacion')
                    ->where('pay.status', '=', 'Pendiente');
            })
            ->where('s.type', 'regular')
            ->whereIn('s.status', ['En proceso', 'En prueba'])
            ->select(
                's.id as id_estudiante',
                DB::raw('null as tipo_id'),
                'p.first_name as nombre',
                'p.last_name as apellido',
                'p.email_personal as correo_personal',
                'p.email_institucional as correo_utp',
                'p.phone as telefono',
                's.created_at as fecha_registro',
                's.status as estado_estudiante',
                'pay.payment_type as tipo_pago',
                'pay.receipt_path as comprobante_imagen',
                'pay.method as metodo_pago',
                'pay.bank as banco',
                'pay.account_owner as propietario_cuenta',
                'pay.status as estado_pago'
            )
            ->orderBy('s.id')
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
            $actualizadas = DB::table('students')
                ->where('id', $validated['id_estudiante'])
                ->where('type', 'regular')
                ->update([
                    'status' => 'Inactivo',
                    'updated_at' => now(),
                ]);

            if ($actualizadas === 0) {
                DB::rollBack();
                return response()->json(['message' => 'Estudiante no encontrado.'], 404);
            }

            DB::table('payments')
                ->where('student_id', $validated['id_estudiante'])
                ->where('payment_type', 'PruebaUbicacion')
                ->where('status', 'Pendiente')
                ->update([
                    'amount' => 10.00,
                    'status' => 'Aceptado',
                    'updated_at' => now(),
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
            $estudiante = DB::table('students')
                ->where('id', $validated['id_estudiante'])
                ->where('type', 'verano')
                ->select('id', 'person_id')
                ->first();

            if (!$estudiante) {
                DB::rollBack();
                return response()->json(['message' => 'Estudiante no encontrado.'], 404);
            }

            DB::table('people')
                ->where('id', $estudiante->person_id)
                ->update([
                    'first_name' => $validated['nombre_completo'],
                    'last_name' => '',
                    'phone' => $validated['celular'],
                    'email_personal' => strtolower(trim($validated['correo'])),
                    'email_institucional' => null,
                    'updated_at' => now(),
                ]);

            DB::table('students')
                ->where('id', $validated['id_estudiante'])
                ->update([
                    'level' => $validated['nivel'],
                    'status' => $validated['estado'],
                    'updated_at' => now(),
                ]);

            DB::table('student_profiles')->updateOrInsert(
                ['student_id' => $validated['id_estudiante']],
                [
                    'birth_date' => $validated['fecha_nacimiento'],
                    'home_number' => $validated['numero_casa'],
                    'address' => $validated['domicilio'],
                    'gender' => $validated['sexo'],
                    'school' => $validated['colegio'],
                    'updated_at' => now(),
                ]
            );

            DB::table('guardians')->updateOrInsert(
                ['student_id' => $validated['id_estudiante']],
                [
                    'father_name' => $validated['nombre_padre'] ?? '',
                    'father_workplace' => $validated['lugar_trabajo_padre'] ?? '',
                    'father_work_phone' => $validated['telefono_trabajo_padre'] ?? '',
                    'father_phone' => $validated['celular_padre'] ?? '',
                    'mother_name' => $validated['nombre_madre'] ?? '',
                    'mother_workplace' => $validated['lugar_trabajo_madre'] ?? '',
                    'mother_work_phone' => $validated['telefono_trabajo_madre'] ?? '',
                    'mother_phone' => $validated['celular_madre'] ?? '',
                    'updated_at' => now(),
                ]
            );

            DB::table('student_contacts')->updateOrInsert(
                ['student_id' => $validated['id_estudiante']],
                [
                    'allergies' => $validated['alergias'] ?? 'No',
                    'blood_type' => $validated['tipo_sangre'],
                    'emergency_name' => $validated['contacto_nombre'] ?? null,
                    'emergency_phone' => $validated['contacto_telefono'] ?? null,
                    'updated_at' => now(),
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

        $registros = DB::table('students as s')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->leftJoin('student_profiles as sp', 'sp.student_id', '=', 's.id')
            ->leftJoin('guardians as g', 'g.student_id', '=', 's.id')
            ->leftJoin('student_contacts as sc', 'sc.student_id', '=', 's.id')
            ->where('s.type', 'verano')
            ->where('s.status', 'En proceso')
            ->select(
                's.id as id_estudiante',
                DB::raw("concat(p.first_name, ' ', p.last_name) as nombre_completo"),
                'p.phone as celular',
                'sp.birth_date as fecha_nacimiento',
                'sp.home_number as numero_casa',
                'sp.address as domicilio',
                'sp.gender as sexo',
                'p.email_personal as correo',
                'sp.school as colegio',
                's.created_at as fecha_registro',
                'sp.signature_path as firma_familiar_imagen',
                'sp.guardian_id_path as cedula_familiar_imagen',
                'sp.student_id_path as cedula_estudiante_imagen',
                'g.father_name as nombre_padre',
                'g.father_workplace as lugar_trabajo_padre',
                'g.father_work_phone as telefono_trabajo_padre',
                'g.father_phone as celular_padre',
                'g.mother_name as nombre_madre',
                'g.mother_workplace as lugar_trabajo_madre',
                'g.mother_work_phone as telefono_trabajo_madre',
                'g.mother_phone as celular_madre',
                'sc.emergency_name as contacto_nombre',
                'sc.emergency_phone as contacto_telefono',
                'sc.blood_type as tipo_sangre',
                'sc.allergies as alergias'
            )
            ->orderBy('s.id')
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

        $actualizadas = DB::table('students')
            ->where('id', $validated['id_estudiante'])
            ->where('type', 'verano')
            ->update([
                'status' => 'Inactivo',
                'updated_at' => now(),
            ]);

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
            $pago = DB::table('payments')
                ->where('student_id', $validated['id_estudiante'])
                ->where('payment_type', 'Abono')
                ->where('status', 'Pendiente')
                ->orderByDesc('paid_at')
                ->first();

            if (!$pago) {
                DB::rollBack();
                return response()->json(['message' => 'No hay abono pendiente que aceptar.'], 404);
            }

            DB::table('payments')
                ->where('id', $pago->id)
                ->update([
                    'amount' => $validated['abono'],
                    'status' => 'Aceptado',
                    'updated_at' => now(),
                ]);

            $existeMovimiento = DB::table('balance_movements')
                ->where('payment_id', $pago->id)
                ->exists();

            if (!$existeMovimiento) {
                $this->saldoService->crearMovimientoSaldo(
                    $validated['id_estudiante'],
                    'abono',
                    (float) $validated['abono'],
                    'abono',
                    null,
                    (int) $pago->id
                );
            }

            $this->saldoService->actualizarSaldoCache($validated['id_estudiante']);

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
            $pago = DB::table('payments')
                ->where('student_id', $validated['id_estudiante'])
                ->where('payment_type', 'Abono')
                ->where('status', 'Pendiente')
                ->orderByDesc('paid_at')
                ->first();

            if (!$pago) {
                DB::rollBack();
                return response()->json(['message' => 'No hay abono pendiente que rechazar.'], 404);
            }

            DB::table('payments')
                ->where('id', $pago->id)
                ->update([
                    'status' => 'Rechazado',
                    'updated_at' => now(),
                ]);

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

        $registros = DB::table('payments as pay')
            ->join('students as s', 's.id', '=', 'pay.student_id')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->where('pay.payment_type', 'Abono')
            ->where('pay.status', 'Pendiente')
            ->select(
                'pay.id as id_pago',
                's.id as id_estudiante',
                'p.first_name as nombre',
                'p.last_name as apellido',
                'p.email_personal as correo_personal',
                'p.email_institucional as correo_utp',
                'p.phone as telefono',
                'pay.payment_type as tipo_pago',
                'pay.receipt_path as comprobante_imagen',
                'pay.method as metodo_pago',
                'pay.bank as banco',
                'pay.account_owner as propietario_cuenta',
                'pay.amount as monto',
                'pay.paid_at as fecha_pago',
                'pay.status as estado_pago'
            )
            ->orderBy('pay.paid_at', 'desc')
            ->get();

        return response()->json($registros);
    }
}
