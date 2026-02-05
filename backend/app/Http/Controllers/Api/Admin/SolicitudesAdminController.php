<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminSolicitudes\AbonosService;
use App\Services\AdminSolicitudes\UbicacionService;
use App\Services\AdminSolicitudes\VeranoService;
use Illuminate\Http\Request;

class SolicitudesAdminController extends Controller
{
    public function __construct(
        private UbicacionService $ubicacionService,
        private VeranoService $veranoService,
        private AbonosService $abonosService
    ) {
    }

    public function listarUbicacion()
    {
        return $this->ubicacionService->listarUbicacion();
    }

    public function aprobarUbicacion(Request $request)
    {
        return $this->ubicacionService->aprobarUbicacion($request);
    }

    public function rechazarUbicacion(Request $request)
    {
        return $this->ubicacionService->rechazarUbicacion($request);
    }

    public function listarVerano()
    {
        return $this->veranoService->listarVerano();
    }

    public function aprobarVerano(Request $request)
    {
        return $this->veranoService->aprobarVerano($request);
    }

    public function rechazarVerano(Request $request)
    {
        return $this->veranoService->rechazarVerano($request);
    }

    public function listarAbonos()
    {
        return $this->abonosService->listarAbonos();
    }

    public function aprobarAbono(Request $request)
    {
        return $this->abonosService->aprobarAbono($request);
    }

    public function rechazarAbono(Request $request)
    {
        return $this->abonosService->rechazarAbono($request);
    }
}
