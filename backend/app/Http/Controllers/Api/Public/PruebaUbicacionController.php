<?php

// Controlador para la gestión de pruebas de ubicación públicas
// Permite crear nuevas pruebas de ubicación.

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Services\PruebaUbicacionService;
use Illuminate\Http\Request;

class PruebaUbicacionController extends Controller
{
    public function __construct(private PruebaUbicacionService $pruebaUbicacionService)
    {
    }

    public function store(Request $request)
    {
        return $this->pruebaUbicacionService->store($request);
    }
}
