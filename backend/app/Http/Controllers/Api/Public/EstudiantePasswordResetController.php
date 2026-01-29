<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;

class EstudiantePasswordResetController extends Controller
{
    public function __construct(private PasswordResetService $passwordResetService)
    {
    }

    public function solicitar(Request $request)
    {
        return $this->passwordResetService->solicitar($request, 'Estudiante', 'estudiantes', '/estudiantes/reset');
    }

    public function solicitarProfesor(Request $request)
    {
        return $this->passwordResetService->solicitar($request, 'Profesor', 'profesores', '/profesores/reset');
    }

    public function resetear(Request $request)
    {
        return $this->passwordResetService->resetear($request, 'Estudiante');
    }

    public function resetearProfesor(Request $request)
    {
        return $this->passwordResetService->resetear($request, 'Profesor');
    }
}
