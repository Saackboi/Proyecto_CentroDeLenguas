<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminPromocionesService;
use Illuminate\Http\Request;

class PromocionesController extends Controller
{
    public function __construct(private AdminPromocionesService $adminPromocionesService)
    {
    }

    public function elegibles(Request $request)
    {
        return $this->adminPromocionesService->elegibles($request);
    }

    public function aplicar(Request $request)
    {
        return $this->adminPromocionesService->aplicar($request);
    }

    public function revertir(Request $request)
    {
        return $this->adminPromocionesService->revertir($request);
    }
}
