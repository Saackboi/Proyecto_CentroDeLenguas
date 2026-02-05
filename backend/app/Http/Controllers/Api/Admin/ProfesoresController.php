<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminSolicitudes\ProfesoresService;
use Illuminate\Http\Request;

class ProfesoresController extends Controller
{
    public function __construct(private ProfesoresService $profesoresService)
    {
    }

    public function crearProfesor(Request $request)
    {
        return $this->profesoresService->crearProfesor($request);
    }

    public function actualizarProfesor(Request $request, string $id)
    {
        return $this->profesoresService->actualizarProfesor($request, $id);
    }

    public function detalleProfesor(string $id)
    {
        return $this->profesoresService->detalleProfesor($id);
    }
}
