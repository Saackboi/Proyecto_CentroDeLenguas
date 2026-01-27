<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\SolicitudesController;
use App\Http\Controllers\Api\Estudiante\NotificacionesController as EstudianteNotificacionesController;
use App\Http\Controllers\Api\NotificacionesController;
use App\Http\Controllers\Api\Public\AbonoController;
use App\Http\Controllers\Api\Public\PruebaUbicacionController;
use App\Http\Controllers\Api\Public\RegistroEstudianteController;
use App\Http\Controllers\Api\Public\VeranoController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:api')->prefix('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::middleware('auth:api')->prefix('admin')->group(function () {
    Route::post('notificaciones', [NotificacionesController::class, 'store']);
    Route::get('notificaciones', [NotificacionesController::class, 'index']);
    Route::patch('notificaciones/{id}/leer', [NotificacionesController::class, 'marcarLeida']);

    Route::get('solicitudes/ubicacion', [SolicitudesController::class, 'listarUbicacion']);
    Route::get('solicitudes/verano', [SolicitudesController::class, 'listarVerano']);
    Route::get('solicitudes/abonos', [SolicitudesController::class, 'listarAbonos']);

    Route::post('ubicacion/aprobar', [SolicitudesController::class, 'aprobarUbicacion']);
    Route::post('ubicacion/rechazar', [SolicitudesController::class, 'rechazarUbicacion']);

    Route::post('verano/aprobar', [SolicitudesController::class, 'aprobarVerano']);
    Route::post('verano/rechazar', [SolicitudesController::class, 'rechazarVerano']);

    Route::post('abono/aprobar', [SolicitudesController::class, 'aprobarAbono']);
    Route::post('abono/rechazar', [SolicitudesController::class, 'rechazarAbono']);
});

Route::middleware('auth:api')->prefix('estudiante')->group(function () {
    Route::get('notificaciones', [EstudianteNotificacionesController::class, 'index']);
    Route::patch('notificaciones/{id}/leer', [EstudianteNotificacionesController::class, 'marcarLeida']);
    Route::patch('notificaciones/leer-todas', [EstudianteNotificacionesController::class, 'marcarTodasLeidas']);
    Route::delete('notificaciones/{id}', [EstudianteNotificacionesController::class, 'eliminar']);
    Route::delete('notificaciones', [EstudianteNotificacionesController::class, 'eliminarTodas']);
});

Route::prefix('public')->group(function () {
    Route::post('inscripcion/ubicacion', [PruebaUbicacionController::class, 'store']);
    Route::post('abono', [AbonoController::class, 'store']);
    Route::post('inscripcion/verano', [VeranoController::class, 'store']);
    Route::post('estudiante/registro', [RegistroEstudianteController::class, 'store']);
});
