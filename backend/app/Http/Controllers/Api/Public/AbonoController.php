<?php

// Controlador para la gestión de abonos públicos
// Permite crear nuevos abonos.

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Services\Public\AbonoService;
use Illuminate\Http\Request;

class AbonoController extends Controller
{
    public function __construct(private AbonoService $abonoService)
    {
    }

    public function store(Request $request)
    {
        return $this->abonoService->store($request);
    }
}
