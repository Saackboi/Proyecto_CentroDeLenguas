<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    public function login(Request $request)
    {
        return $this->authService->login($request);
    }

    public function me()
    {
        return $this->authService->me();
    }

    public function logout()
    {
        return $this->authService->logout();
    }

    public function refresh()
    {
        return $this->authService->refresh();
    }
}
