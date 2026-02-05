<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminSolicitudes\DashboardsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardsService $dashboardsService)
    {
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

    public function dashboardResumen()
    {
        return $this->dashboardsService->dashboardResumen();
    }
}
