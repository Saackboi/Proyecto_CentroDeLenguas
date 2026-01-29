<?php

namespace App\Http\Controllers\Api\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\EstudianteNotificacionesService;
use Illuminate\Http\Request;

class NotificacionesController extends Controller
{
    public function __construct(private EstudianteNotificacionesService $estudianteNotificacionesService)
    {
    }

    public function index(Request $request)
    {
        return $this->estudianteNotificacionesService->index($request);
    }

    public function marcarLeida(string $id)
    {
        return $this->estudianteNotificacionesService->marcarLeida($id);
    }

    public function marcarTodasLeidas()
    {
        return $this->estudianteNotificacionesService->marcarTodasLeidas();
    }

    public function eliminar(string $id)
    {
        return $this->estudianteNotificacionesService->eliminar($id);
    }

    public function eliminarTodas()
    {
        return $this->estudianteNotificacionesService->eliminarTodas();
    }
}
