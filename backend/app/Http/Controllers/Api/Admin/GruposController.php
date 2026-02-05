<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminSolicitudes\GruposService;
use Illuminate\Http\Request;

class GruposController extends Controller
{
    public function __construct(private GruposService $gruposService)
    {
    }

    public function crearGrupo(Request $request)
    {
        return $this->gruposService->crearGrupo($request);
    }

    public function actualizarGrupo(Request $request, string $id)
    {
        return $this->gruposService->actualizarGrupo($request, $id);
    }

    public function previsualizarAjusteRetiro(Request $request, string $id)
    {
        return $this->gruposService->previsualizarAjusteRetiro($request, $id);
    }

    public function confirmarAjusteRetiro(Request $request, string $id)
    {
        return $this->gruposService->confirmarAjusteRetiro($request, $id);
    }

    public function listarGrupos(Request $request)
    {
        return $this->gruposService->listarGrupos($request);
    }

    public function detalleGrupo(Request $request, string $id)
    {
        return $this->gruposService->detalleGrupo($request, $id);
    }

    public function listarEstudiantesGrupo(Request $request, string $id)
    {
        return $this->gruposService->listarEstudiantesGrupo($request, $id);
    }

    public function listarEstudiantesDisponibles(Request $request)
    {
        return $this->gruposService->listarEstudiantesDisponibles($request);
    }
}
