<?php

// Controlador publico para estado de la landing

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Services\LandingAnnouncementService;

class LandingAnnouncementController extends Controller
{
    public function __construct(private LandingAnnouncementService $landingAnnouncementService)
    {
    }

    public function get()
    {
        return $this->landingAnnouncementService->getPublicAnnouncement();
    }
}
