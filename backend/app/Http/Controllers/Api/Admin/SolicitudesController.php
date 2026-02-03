<?php

// Controlador para la gestión de solicitudes administrativas
// Incluye funcionalidades para crear y actualizar profesores y grupos,
// gestionar ajustes de retiro, listar estudiantes y profesores, y generar reportes.

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminSolicitudesService;
use Illuminate\Http\Request;

class SolicitudesController extends Controller
{
    public function __construct(private AdminSolicitudesService $adminSolicitudesService)
    {
    }

    public function crearProfesor(Request $request)
    {
        return $this->adminSolicitudesService->crearProfesor($request);
    }

    public function actualizarProfesor(Request $request, string $id)
    {
        return $this->adminSolicitudesService->actualizarProfesor($request, $id);
    }

    public function crearGrupo(Request $request)
    {
        return $this->adminSolicitudesService->crearGrupo($request);
    }

    public function actualizarGrupo(Request $request, string $id)
    {
        return $this->adminSolicitudesService->actualizarGrupo($request, $id);
    }

    public function previsualizarAjusteRetiro(Request $request, string $id)
    {
        return $this->adminSolicitudesService->previsualizarAjusteRetiro($request, $id);
    }

    public function confirmarAjusteRetiro(Request $request, string $id)
    {
        return $this->adminSolicitudesService->confirmarAjusteRetiro($request, $id);
    }

    public function listarGrupos(Request $request)
    {
        return $this->adminSolicitudesService->listarGrupos($request);
    }

    public function detalleGrupo(Request $request, string $id)
    {
        return $this->adminSolicitudesService->detalleGrupo($request, $id);
    }

    public function listarEstudiantesGrupo(Request $request, string $id)
    {
        return $this->adminSolicitudesService->listarEstudiantesGrupo($request, $id);
    }

    public function listarEstudiantesDisponibles(Request $request)
    {
        return $this->adminSolicitudesService->listarEstudiantesDisponibles($request);
    }

    public function detalleEstudiante(Request $request, string $id)
    {
        return $this->adminSolicitudesService->detalleEstudiante($request, $id);
    }

    public function detalleProfesor(string $id)
    {
        return $this->adminSolicitudesService->detalleProfesor($id);
    }

    public function dashboardEstudiantes(Request $request)
    {
        return $this->adminSolicitudesService->dashboardEstudiantes($request);
    }

    public function dashboardProfesores(Request $request)
    {
        return $this->adminSolicitudesService->dashboardProfesores($request);
    }

    public function dashboardGrupos(Request $request)
    {
        return $this->adminSolicitudesService->dashboardGrupos($request);
    }

    public function reportes(Request $request)
    {
        return $this->adminSolicitudesService->reportes($request);
    }

    public function exportarReportePdf(Request $request)
    {
        return $this->adminSolicitudesService->exportarReportePdf($request);
    }

    public function actualizarEstudiante(Request $request, string $id)
    {
        return $this->adminSolicitudesService->actualizarEstudiante($request, $id);
    }

    public function actualizarEstudianteVerano(Request $request, string $id)
    {
        return $this->adminSolicitudesService->actualizarEstudianteVerano($request, $id);
    }

    public function aprobarUbicacion(Request $request)
    {
        return $this->adminSolicitudesService->aprobarUbicacion($request);
    }

    public function listarUbicacion()
    {
        return $this->adminSolicitudesService->listarUbicacion();
    }

    public function rechazarUbicacion(Request $request)
    {
        return $this->adminSolicitudesService->rechazarUbicacion($request);
    }

    public function aprobarVerano(Request $request)
    {
        return $this->adminSolicitudesService->aprobarVerano($request);
    }

    public function listarVerano()
    {
        return $this->adminSolicitudesService->listarVerano();
    }

    public function rechazarVerano(Request $request)
    {
        return $this->adminSolicitudesService->rechazarVerano($request);
    }

    public function aprobarAbono(Request $request)
    {
        return $this->adminSolicitudesService->aprobarAbono($request);
    }

    public function rechazarAbono(Request $request)
    {
        return $this->adminSolicitudesService->rechazarAbono($request);
    }

    public function listarAbonos()
    {
        return $this->adminSolicitudesService->listarAbonos();
    }
}
