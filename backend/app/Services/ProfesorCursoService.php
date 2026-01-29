<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfesorCursoService
{
    private function asegurarProfesor()
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->tipo_usuario !== 'Profesor') {
            return [null, response()->json(['message' => 'No autorizado.'], 403)];
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

    private function obtenerProfesorId(string $correo): ?string
    {
        $profesor = DB::table('profesores')
            ->select('id_profesor')
            ->where('correo', $correo)
            ->first();

        return $profesor?->id_profesor;
    }

    private function tablaGrupoPorTipo(string $tipo): string
    {
        return $tipo === 'verano' ? 'grupos_estudiante_verano' : 'grupos_estudiantes';
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

    private function buscarCurso(string $correoProfesor, string $tipo, string $idGrupo): ?array
    {
        $tablaGrupo = $this->tablaGrupoPorTipo($tipo);

        $curso = DB::table("{$tablaGrupo} as ge")
            ->join('grupo_profesor as gp', 'gp.id_grupo', '=', 'ge.id_grupo')
            ->join('profesores as p', 'p.id_profesor', '=', 'gp.id_profesor')
            ->join('grupos as g', 'g.id_grupo', '=', 'gp.id_grupo')
            ->leftJoin('cursos_idiomas as ci', 'ci.id_idioma', '=', 'g.id_idioma')
            ->where('p.correo', $correoProfesor)
            ->where('ge.id_grupo', $idGrupo)
            ->whereNotNull('ge.fecha_cierre')
            ->whereRaw('DATE_ADD(ge.fecha_cierre, INTERVAL 6 DAY) > CURDATE()')
            ->distinct()
            ->select(
                'ge.id_grupo',
                'ge.fecha_inicio',
                'ge.fecha_cierre',
                'ge.aula',
                'p.correo',
                DB::raw("concat(p.nombre, ' ', p.apellido) as profesor"),
                DB::raw('ci.nombre as curso'),
                'g.nivel'
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
        $profesorId = $this->obtenerProfesorId($usuario->correo);

        if (!$profesorId) {
            return response()->json([
                'message' => 'Profesor no encontrado.',
                'cursos' => [],
            ], 404);
        }

        $tablaGrupo = $this->tablaGrupoPorTipo($tipo);

        $cursos = DB::table("{$tablaGrupo} as ge")
            ->join('grupo_profesor as gp', 'gp.id_grupo', '=', 'ge.id_grupo')
            ->join('profesores as p', 'p.id_profesor', '=', 'gp.id_profesor')
            ->join('grupos as g', 'g.id_grupo', '=', 'gp.id_grupo')
            ->leftJoin('cursos_idiomas as ci', 'ci.id_idioma', '=', 'g.id_idioma')
            ->where('p.correo', $usuario->correo)
            ->whereNotNull('ge.fecha_cierre')
            ->whereRaw('DATE_ADD(ge.fecha_cierre, INTERVAL 6 DAY) > CURDATE()')
            ->distinct()
            ->select(
                'ge.id_grupo',
                'ge.fecha_inicio',
                'ge.fecha_cierre',
                'ge.aula',
                'p.correo',
                DB::raw("concat(p.nombre, ' ', p.apellido) as profesor"),
                DB::raw('ci.nombre as curso'),
                'g.nivel'
            )
            ->orderByDesc('ge.fecha_cierre')
            ->get();

        if ($cursos->isEmpty()) {
            return response()->json([
                'message' => 'No tiene ningún grupo activo en este momento.',
                'cursos' => [],
            ]);
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

        return response()->json([
            'cursos' => $respuesta,
        ]);
    }

    public function listarEstudiantes(Request $request)
    {
        [$usuario, $error] = $this->asegurarProfesor();
        if ($error) {
            return $error;
        }

        $validated = $request->validate([
            'tipo' => ['required', 'in:regular,verano'],
            'id_grupo' => ['required', 'string', 'max:10'],
        ]);

        $curso = $this->buscarCurso($usuario->correo, $validated['tipo'], $validated['id_grupo']);
        if (!$curso) {
            return response()->json(['message' => 'Grupo no encontrado o no disponible.'], 404);
        }

        if ($validated['tipo'] === 'verano') {
            $estudiantes = DB::table('grupos_estudiante_verano as ge')
                ->join('estudiante_verano as ev', 'ev.id_estudiante', '=', 'ge.id_estudiante')
                ->select(
                    DB::raw("ev.nombre_completo as estudiante"),
                    'ev.id_estudiante',
                    'ge.id_grupo',
                    'ge.nota_final',
                    'ge.fecha_cierre'
                )
                ->where('ge.id_grupo', $validated['id_grupo'])
                ->whereRaw('DATE_ADD(ge.fecha_cierre, INTERVAL 6 DAY) > CURDATE()')
                ->get();
        } else {
            $estudiantes = DB::table('grupos_estudiantes as ge')
                ->join('estudiantes as e', 'e.id_estudiante', '=', 'ge.id_estudiante')
                ->select(
                    DB::raw("concat(e.nombre, ' ', e.apellido) as estudiante"),
                    'e.id_estudiante',
                    'ge.id_grupo',
                    'ge.nota_final',
                    'ge.fecha_cierre'
                )
                ->where('ge.id_grupo', $validated['id_grupo'])
                ->whereRaw('DATE_ADD(ge.fecha_cierre, INTERVAL 6 DAY) > CURDATE()')
                ->get();
        }

        $notasEnviadas = $estudiantes->contains(function ($estudiante) {
            $nota = $estudiante->nota_final;
            return $nota !== null && (int) $nota !== 0;
        });

        return response()->json([
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
            'id_grupo' => ['required', 'string', 'max:10'],
            'notas' => ['required', 'array', 'min:1'],
            'notas.*.id_estudiante' => ['required', 'string', 'max:30'],
            'notas.*.nota' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $curso = $this->buscarCurso($usuario->correo, $validated['tipo'], $validated['id_grupo']);
        if (!$curso) {
            return response()->json(['message' => 'Grupo no encontrado o no disponible.'], 404);
        }

        $now = Carbon::now('America/Panama');
        $cierre = Carbon::parse($curso['fecha_cierre']);
        $limite = $cierre->copy()->addDays(6);

        if ($now->lte($cierre) || $now->gte($limite)) {
            return response()->json([
                'message' => 'Fuera del período permitido para enviar notas.',
            ], 422);
        }

        $tablaGrupo = $this->tablaGrupoPorTipo($validated['tipo']);

        $yaEnviadas = DB::table($tablaGrupo)
            ->where('id_grupo', $validated['id_grupo'])
            ->whereNotNull('nota_final')
            ->where('nota_final', '!=', 0)
            ->exists();

        if ($yaEnviadas) {
            return response()->json([
                'message' => 'Las notas ya fueron enviadas para este grupo.',
            ], 409);
        }

        DB::beginTransaction();
        $actualizados = 0;
        $noActualizados = [];

        try {
            foreach ($validated['notas'] as $nota) {
                $affected = DB::table($tablaGrupo)
                    ->where('id_grupo', $validated['id_grupo'])
                    ->where('id_estudiante', $nota['id_estudiante'])
                    ->where(function ($query) {
                        $query->whereNull('nota_final')
                            ->orWhere('nota_final', 0);
                    })
                    ->update(['nota_final' => $nota['nota']]);

                if ($affected === 0) {
                    $noActualizados[] = $nota['id_estudiante'];
                } else {
                    $actualizados += $affected;
                }
            }

            if ($actualizados === 0) {
                DB::rollBack();
                return response()->json([
                    'message' => 'No se encontraron registros para actualizar.',
                ], 404);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al guardar las notas. Inténtelo nuevamente.',
            ], 500);
        }

        return response()->json([
            'message' => 'Notas guardadas correctamente.',
            'actualizados' => $actualizados,
            'no_actualizados' => $noActualizados,
        ]);
    }

    public function cambiarPassword(Request $request)
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->tipo_usuario !== 'Profesor') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $validated = $request->validate([
            'contrasena_nueva' => ['required', 'string', 'min:8'],
        ]);

        DB::table('usuarios')
            ->where('correo', $usuario->correo)
            ->update([
                'contrasena' => Hash::make($validated['contrasena_nueva']),
            ]);

        return response()->json(['message' => 'Contrasena actualizada.']);
    }
}
