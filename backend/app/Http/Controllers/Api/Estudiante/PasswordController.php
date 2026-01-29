<?php

namespace App\Http\Controllers\Api\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\EstudiantePasswordService;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public function __construct(private EstudiantePasswordService $estudiantePasswordService)
    {
    }

    public function cambiar(Request $request)
    {
        return $this->estudiantePasswordService->cambiar($request);
    }
}
