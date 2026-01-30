<?php

namespace App\Services;

use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfesorCursoService
{
    private function asegurarProfesor()
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->role !== 'Profesor') {
            return [null, ApiResponse::forbidden('No autorizado.')];
        }

        return [$usuario, null];
    }

    private function resolverTipo(Request $request): string
    {
        $validated = $request->validate([
            'tipo' => ['required', 'in:regular,verano'],
        ]);

        return $validated['tipo'];
    }

    private function obtenerProfesorPorCorreo(string $correo): ?object
    {
        return DB::table('teachers as t')
            ->join('people as p', 'p.id', '=', 't.person_id')
            ->where(function ($query) use ($correo) {
                $query->where('p.email_personal', $correo)
                    ->orWhere('p.email_institucional', $correo);
            })
            ->select('t.id', 'p.first_name', 'p.last_name')
            ->first();
    }

    private function estadoCurso(array $curso): array
    {
        $inicio = Carbon::parse($curso['fecha_inicio']);
        $cierre = Carbon::parse($curso['fecha_cierre']);
        $cierreDefinitivo = $cierre->copy()->addDays(5);
        $cierreDefinitivoPost = $cierre->copy()->addDays(6);
        $now = Carbon::now('America/Panama');

        $mensajes = [];
        $cursoDisponible = false;
        $cursoEnviarEstado = false;

        if ($now->lt($inicio)) {
            $mensajes[] = "Advertencia: El período activo del curso no ha comenzado, el curso comenzará el {$inicio->format('Y-m-d')}";
            $cursoDisponible = true;
        }

        if ($now->gt($inicio) && $now->lt($cierre)) {
            $mensajes[] = 'Advertencia: El período activo del curso no ha terminado, por lo cual no es posible agregar las evaluaciones finales.';
            $cursoDisponible = true;
        }

        if ($now->gt($cierre)) {
            $mensajes[] = "Recuerde: El plazo para ingresar las notas vence el {$cierreDefinitivo->format('Y-m-d')}, el sistema solo permite ingresar las notas durante los cinco días posteriores al cierre del curso.";
            $cursoDisponible = true;
            $cursoEnviarEstado = true;
        }

        if ($now->gt($cierreDefinitivoPost)) {
            $mensajes[] = 'Le informamos que ha concluido el período establecido para registrar las notas. Ya no será posible realizar modificaciones ni agregar nuevas notas.';
        }

        return [
            'curso_disponible' => $cursoDisponible,
            'curso_enviar_estado' => $cursoEnviarEstado,
            'mensajes' => $mensajes,
        ];
    }

    private function buscarCurso(int $profesorId, string $tipo, ?string $idGrupo, ?int $idSesion): ?array
    {
        $fechaLimite = Carbon::now('America/Panama')->subDays(6)->toDateString();

        $query = DB::table('group_sessions as gs')
            ->join('groups as g', 'g.id', '=', 'gs.group_id')
            ->join('teachers as t', 't.id', '=', 'gs.teacher_id')
            ->join('people as p', 'p.id', '=', 't.person_id')
            ->leftJoin('languages as l', 'l.id', '=', 'g.language_id')
            ->join('enrollments as e', 'e.group_session_id', '=', 'gs.id')
            ->join('students as s', 's.id', '=', 'e.student_id')
            ->where('t.id', $profesorId)
            ->where('s.type', $tipo)
            ->whereNotNull('gs.end_date')
            ->whereDate('gs.end_date', '>=', $fechaLimite);

        if ($idSesion) {
            $query->where('gs.id', $idSesion);
        } elseif ($idGrupo) {
            $query->where('gs.group_id', $idGrupo)
                ->orderByDesc('gs.end_date');
        }

        $curso = $query
            ->select(
                'g.id as id_grupo',
                'gs.id as group_session_id',
                'gs.start_date as fecha_inicio',
                'gs.end_date as fecha_cierre',
                'gs.classroom as aula',
                DB::raw("coalesce(p.email_institucional, p.email_personal) as correo"),
                DB::raw("concat(p.first_name, ' ', p.last_name) as profesor"),
                DB::raw('l.name as curso'),
                'g.level as nivel'
            )
            ->first();

        if (!$curso) {
            return null;
        }

        return (array) $curso;
    }

    public function listarCursos(Request $request)
    {
        [$usuario, $error] = $this->asegurarProfesor();
        if ($error) {
            return $error;
        }

        $tipo = $this->resolverTipo($request);
        $profesor = $this->obtenerProfesorPorCorreo($usuario->email);

        if (!$profesor) {
            return ApiResponse::notFound('Profesor no encontrado.');
        }

        $fechaLimite = Carbon::now('America/Panama')->subDays(6)->toDateString();

        $cursos = DB::table('group_sessions as gs')
            ->join('groups as g', 'g.id', '=', 'gs.group_id')
            ->join('teachers as t', 't.id', '=', 'gs.teacher_id')
            ->join('people as p', 'p.id', '=', 't.person_id')
            ->leftJoin('languages as l', 'l.id', '=', 'g.language_id')
            ->join('enrollments as e', 'e.group_session_id', '=', 'gs.id')
            ->join('students as s', 's.id', '=', 'e.student_id')
            ->where('t.id', $profesor->id)
            ->where('s.type', $tipo)
            ->whereNotNull('gs.end_date')
            ->whereDate('gs.end_date', '>=', $fechaLimite)
            ->distinct()
            ->select(
                'g.id as id_grupo',
                'gs.id as group_session_id',
                'gs.start_date as fecha_inicio',
                'gs.end_date as fecha_cierre',
                'gs.classroom as aula',
                DB::raw("coalesce(p.email_institucional, p.email_personal) as correo"),
                DB::raw("concat(p.first_name, ' ', p.last_name) as profesor"),
                DB::raw('l.name as curso'),
                'g.level as nivel'
            )
            ->orderByDesc('gs.end_date')
            ->get();

        if ($cursos->isEmpty()) {
            return ApiResponse::success(['cursos' => []], 'No tiene ningún grupo activo en este momento.');
        }

        $respuesta = $cursos->map(function ($curso) {
            $cursoArray = (array) $curso;
            $estado = $this->estadoCurso($cursoArray);

            return array_merge($cursoArray, [
                'flags' => [
                    'curso_disponible' => $estado['curso_disponible'],
                    'curso_enviar_estado' => $estado['curso_enviar_estado'],
                ],
                'mensajes' => $estado['mensajes'],
            ]);
        })->values();

        return ApiResponse::success(['cursos' => $respuesta]);
    }

    public function listarEstudiantes(Request $request)
    {
        [$usuario, $error] = $this->asegurarProfesor();
        if ($error) {
            return $error;
        }

        $validated = $request->validate([
            'tipo' => ['required', 'in:regular,verano'],
            'id_grupo' => ['required_without:group_session_id', 'string', 'max:10'],
            'group_session_id' => ['required_without:id_grupo', 'integer', 'exists:group_sessions,id'],
        ]);

        $profesor = $this->obtenerProfesorPorCorreo($usuario->email);
        if (!$profesor) {
            return ApiResponse::notFound('Profesor no encontrado.');
        }

        $curso = $this->buscarCurso(
            $profesor->id,
            $validated['tipo'],
            $validated['id_grupo'] ?? null,
            $validated['group_session_id'] ?? null
        );
        if (!$curso) {
            return ApiResponse::notFound('Grupo no encontrado o no disponible.');
        }

        $estudiantes = DB::table('enrollments as e')
            ->join('students as s', 's.id', '=', 'e.student_id')
            ->join('people as p', 'p.id', '=', 's.person_id')
            ->where('e.group_session_id', $curso['group_session_id'])
            ->where('s.type', $validated['tipo'])
            ->select(
                DB::raw("concat(p.first_name, ' ', p.last_name) as estudiante"),
                's.id as id_estudiante',
                DB::raw("'{$curso['id_grupo']}' as id_grupo"),
                'e.final_grade as nota_final'
            )
            ->orderBy('p.first_name')
            ->orderBy('p.last_name')
            ->get();

        $notasEnviadas = $estudiantes->contains(function ($estudiante) {
            $nota = $estudiante->nota_final;
            return $nota !== null && (int) $nota !== 0;
        });

        return ApiResponse::success([
            'curso' => $curso,
            'notas_enviadas' => $notasEnviadas,
            'estudiantes' => $estudiantes,
        ]);
    }

    public function guardarNotas(Request $request)
    {
        [$usuario, $error] = $this->asegurarProfesor();
        if ($error) {
            return $error;
        }

        $validated = $request->validate([
            'tipo' => ['required', 'in:regular,verano'],
            'id_grupo' => ['required_without:group_session_id', 'string', 'max:10'],
            'group_session_id' => ['required_without:id_grupo', 'integer', 'exists:group_sessions,id'],
            'notas' => ['required', 'array', 'min:1'],
            'notas.*.id_estudiante' => ['required', 'string', 'max:30'],
            'notas.*.nota' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $profesor = $this->obtenerProfesorPorCorreo($usuario->email);
        if (!$profesor) {
            return ApiResponse::notFound('Profesor no encontrado.');
        }

        $curso = $this->buscarCurso(
            $profesor->id,
            $validated['tipo'],
            $validated['id_grupo'] ?? null,
            $validated['group_session_id'] ?? null
        );
        if (!$curso) {
            return ApiResponse::notFound('Grupo no encontrado o no disponible.');
        }

        $now = Carbon::now('America/Panama');
        $cierre = Carbon::parse($curso['fecha_cierre']);
        $limite = $cierre->copy()->addDays(6);

        if ($now->lte($cierre) || $now->gte($limite)) {
            return ApiResponse::error('Fuera del período permitido para enviar notas.', 422, null, 'periodo_invalido');
        }

        $yaEnviadas = DB::table('enrollments')
            ->where('group_session_id', $curso['group_session_id'])
            ->whereNotNull('final_grade')
            ->where('final_grade', '!=', 0)
            ->exists();

        if ($yaEnviadas) {
            return ApiResponse::error('Las notas ya fueron enviadas para este grupo.', 409, null, 'conflict');
        }

        DB::beginTransaction();
        $actualizados = 0;
        $noActualizados = [];

        try {
            foreach ($validated['notas'] as $nota) {
                $affected = DB::table('enrollments')
                    ->where('group_session_id', $curso['group_session_id'])
                    ->where('student_id', $nota['id_estudiante'])
                    ->where(function ($query) {
                        $query->whereNull('final_grade')
                            ->orWhere('final_grade', 0);
                    })
                    ->update(['final_grade' => $nota['nota']]);

                if ($affected === 0) {
                    $noActualizados[] = $nota['id_estudiante'];
                } else {
                    $actualizados += $affected;
                }
            }

            if ($actualizados === 0) {
                DB::rollBack();
                return ApiResponse::notFound('No se encontraron registros para actualizar.');
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::serverError('Error al guardar las notas. Inténtelo nuevamente.');
        }

        return ApiResponse::success([
            'actualizados' => $actualizados,
            'no_actualizados' => $noActualizados,
        ], 'Notas guardadas correctamente.');
    }

    public function cambiarPassword(Request $request)
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->role !== 'Profesor') {
            return ApiResponse::forbidden('No autorizado.');
        }

        $validated = $request->validate([
            'contrasena_nueva' => ['required', 'string', 'min:8'],
        ]);

        DB::table('users')
            ->where('id', $usuario->id)
            ->update([
                'password' => Hash::make($validated['contrasena_nueva']),
            ]);

        return ApiResponse::success(null, 'Contrasena actualizada.');
    }
}
