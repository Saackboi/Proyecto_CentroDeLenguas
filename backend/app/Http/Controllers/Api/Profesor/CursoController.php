<?php

namespace App\Http\Controllers\Api\Profesor;

use App\Http\Controllers\Controller;
use App\Services\ProfesorCursoService;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function __construct(private ProfesorCursoService $profesorCursoService)
    {
    }

    public function listarCursos(Request $request)
    {
        return $this->profesorCursoService->listarCursos($request);
    }

    public function listarEstudiantes(Request $request)
    {
        return $this->profesorCursoService->listarEstudiantes($request);
    }

    public function guardarNotas(Request $request)
    {
        return $this->profesorCursoService->guardarNotas($request);
    }

    public function cambiarPassword(Request $request)
    {
        return $this->profesorCursoService->cambiarPassword($request);
    }
}
