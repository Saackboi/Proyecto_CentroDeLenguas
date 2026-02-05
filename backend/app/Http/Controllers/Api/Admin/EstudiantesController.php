<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminSolicitudes\EstudiantesService;
use Illuminate\Http\Request;

class EstudiantesController extends Controller
{
    public function __construct(private EstudiantesService $estudiantesService)
    {
    }

    public function detalleEstudiante(Request $request, string $id)
    {
        return $this->estudiantesService->detalleEstudiante($request, $id);
    }

    public function actualizarEstudiante(Request $request, string $id)
    {
        return $this->estudiantesService->actualizarEstudiante($request, $id);
    }

    public function actualizarEstudianteVerano(Request $request, string $id)
    {
        return $this->estudiantesService->actualizarEstudianteVerano($request, $id);
    }
}
