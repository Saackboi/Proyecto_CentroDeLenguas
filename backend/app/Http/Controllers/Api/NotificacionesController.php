<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminNotificacionesService;
use Illuminate\Http\Request;

class NotificacionesController extends Controller
{
    public function __construct(private AdminNotificacionesService $adminNotificacionesService)
    {
    }

    public function store(Request $request)
    {
        return $this->adminNotificacionesService->store($request);
    }

    public function index(Request $request)
    {
        return $this->adminNotificacionesService->index($request);
    }

    public function marcarLeida(string $id)
    {
        return $this->adminNotificacionesService->marcarLeida($id);
    }
}
