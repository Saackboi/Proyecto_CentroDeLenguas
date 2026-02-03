<?php

// Servicio para la gestión de reportes de solicitudes administrativas
// Permite generar y exportar reportes en formato PDF.

namespace App\Services\AdminSolicitudes;

use App\Services\AdminReportService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ReportesService
{
    use AdminSolicitudesHelpers;

    public function __construct(private AdminReportService $reportService)
    {
    }

    public function reportes(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $config = $this->reportService->configurarReporte($request);
        if (isset($config['error'])) {
            return ApiResponse::error($config['error'], 422, null, 'validation_error');
        }

        return $this->reportService->datatableResponse(
            $request,
            $config['query'],
            $config['search'],
            $config['order'],
            $config['defaultOrder'] ?? []
        );
    }

    public function exportarReportePdf(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        if (!app()->bound('dompdf.wrapper')) {
            return ApiResponse::error(
                'Exportar PDF no disponible. Instale barryvdh/laravel-dompdf.',
                503,
                null,
                'service_unavailable'
            );
        }

        $config = $this->reportService->configurarReporte($request);
        if (isset($config['error'])) {
            return ApiResponse::error($config['error'], 422, null, 'validation_error');
        }

        $rows = $config['query']->get();
        $headers = $config['headers'];
        $title = $config['title'];
        $columns = $config['columns'];

        $footer = null;
        if (($config['footer'] ?? null) === 'total_saldo') {
            $totalSaldo = $rows->sum(function ($row) {
                return (float) ($row->saldo_valor ?? 0);
            });
            $footer = 'Total saldo: B/.' . number_format($totalSaldo, 2, '.', '');
        }

        $pdf = app('dompdf.wrapper')->loadView('reports.reporte', [
            'title' => $title,
            'headers' => $headers,
            'columns' => $columns,
            'rows' => $rows,
            'footer' => $footer,
        ]);

        $filename = 'reporte_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }
}
