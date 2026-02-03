<?php

// Controlador para la gestión de cursos de verano públicos
// Permite crear nuevos cursos de verano.

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Services\VeranoService;
use Illuminate\Http\Request;

class VeranoController extends Controller
{
    public function __construct(private VeranoService $veranoService)
    {
    }

    public function store(Request $request)
    {
        return $this->veranoService->store($request);
    }
}
