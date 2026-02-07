<?php

// Controlador admin para estado de la landing

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\LandingAnnouncementService;
use Illuminate\Http\Request;

class LandingAnnouncementController extends Controller
{
    public function __construct(private LandingAnnouncementService $landingAnnouncementService)
    {
    }

    public function update(Request $request)
    {
        return $this->landingAnnouncementService->updateStatus($request);
    }
}
