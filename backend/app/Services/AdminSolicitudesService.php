<?php

// Servicio principal para la gestión de solicitudes administrativas
// Coordina otros servicios específicos como profesores, grupos, estudiantes, ubicaciones, verano y abonos.


namespace App\Services;

use App\Services\AdminSolicitudes\AbonosService;
use App\Services\AdminSolicitudes\DashboardsService;
use App\Services\AdminSolicitudes\EstudiantesService;
use App\Services\AdminSolicitudes\GruposService;
use App\Services\AdminSolicitudes\ProfesoresService;
use App\Services\AdminSolicitudes\ReportesService;
use App\Services\AdminSolicitudes\UbicacionService;
use App\Services\AdminSolicitudes\VeranoService;
use Illuminate\Http\Request;

class AdminSolicitudesService
{
    public function __construct(
        private AbonosService $abonosService,
        private UbicacionService $ubicacionService,
        private VeranoService $veranoService,
        private ProfesoresService $profesoresService,
        private GruposService $gruposService,
        private EstudiantesService $estudiantesService,
        private DashboardsService $dashboardsService,
        private ReportesService $reportesService
    ) {
    }

    public function crearProfesor(Request $request)
    {
        return $this->profesoresService->crearProfesor($request);
    }

    public function actualizarProfesor(Request $request, string $id)
    {
        return $this->profesoresService->actualizarProfesor($request, $id);
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

    public function detalleEstudiante(Request $request, string $id)
    {
        return $this->estudiantesService->detalleEstudiante($request, $id);
    }

    public function detalleProfesor(string $id)
    {
        return $this->profesoresService->detalleProfesor($id);
    }

    public function dashboardEstudiantes(Request $request)
    {
        return $this->dashboardsService->dashboardEstudiantes($request);
    }

    public function dashboardProfesores(Request $request)
    {
        return $this->dashboardsService->dashboardProfesores($request);
    }

    public function dashboardGrupos(Request $request)
    {
        return $this->dashboardsService->dashboardGrupos($request);
    }


    public function reportes(Request $request)
    {
        return $this->reportesService->reportes($request);
    }

    public function exportarReportePdf(Request $request)
    {
        return $this->reportesService->exportarReportePdf($request);
    }

    public function actualizarEstudiante(Request $request, string $id)
    {
        return $this->estudiantesService->actualizarEstudiante($request, $id);
    }

    public function actualizarEstudianteVerano(Request $request, string $id)
    {
        return $this->estudiantesService->actualizarEstudianteVerano($request, $id);
    }

    public function aprobarUbicacion(Request $request)
    {
        return $this->ubicacionService->aprobarUbicacion($request);
    }

    public function listarUbicacion()
    {
        return $this->ubicacionService->listarUbicacion();
    }

    public function rechazarUbicacion(Request $request)
    {
        return $this->ubicacionService->rechazarUbicacion($request);
    }

    public function aprobarVerano(Request $request)
    {
        return $this->veranoService->aprobarVerano($request);
    }

    public function listarVerano()
    {
        return $this->veranoService->listarVerano();
    }

    public function rechazarVerano(Request $request)
    {
        return $this->veranoService->rechazarVerano($request);
    }

    public function aprobarAbono(Request $request)
    {
        return $this->abonosService->aprobarAbono($request);
    }

    public function rechazarAbono(Request $request)
    {
        return $this->abonosService->rechazarAbono($request);
    }

    public function listarAbonos()
    {
        return $this->abonosService->listarAbonos();
    }
}
