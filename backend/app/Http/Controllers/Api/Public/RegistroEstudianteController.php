<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Services\RegistroEstudianteService;
use Illuminate\Http\Request;

class RegistroEstudianteController extends Controller
{
    public function __construct(private RegistroEstudianteService $registroEstudianteService)
    {
    }

    public function store(Request $request)
    {
        return $this->registroEstudianteService->store($request);
    }
}
