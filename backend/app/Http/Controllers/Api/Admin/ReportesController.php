<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminSolicitudes\ReportesService;
use Illuminate\Http\Request;

class ReportesController extends Controller
{
    public function __construct(private ReportesService $reportesService)
    {
    }

    public function reportes(Request $request)
    {
        return $this->reportesService->reportes($request);
    }

    public function exportarReportePdf(Request $request)
    {
        return $this->reportesService->exportarReportePdf($request);
    }
}
