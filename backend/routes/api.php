<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\SolicitudesController;
use App\Http\Controllers\Api\Admin\PromocionesController;
use App\Http\Controllers\Api\Estudiante\PasswordController as EstudiantePasswordController;
use App\Http\Controllers\Api\Estudiante\NotificacionesController as EstudianteNotificacionesController;
use App\Http\Controllers\Api\NotificacionesController;
use App\Http\Controllers\Api\Profesor\CursoController as ProfesorCursoController;
use App\Http\Controllers\Api\Public\AbonoController;
use App\Http\Controllers\Api\Public\EstudiantePasswordResetController;
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

    Route::post('profesores', [SolicitudesController::class, 'crearProfesor']);
    Route::patch('profesores/{id}', [SolicitudesController::class, 'actualizarProfesor']);

    Route::post('grupos', [SolicitudesController::class, 'crearGrupo']);
    Route::patch('grupos/{id}', [SolicitudesController::class, 'actualizarGrupo']);
    Route::post('grupos/{id}/retiro/preview', [SolicitudesController::class, 'previsualizarAjusteRetiro']);
    Route::post('grupos/{id}/retiro/confirm', [SolicitudesController::class, 'confirmarAjusteRetiro']);
    Route::get('grupos', [SolicitudesController::class, 'listarGrupos']);
    Route::get('grupos/{id}', [SolicitudesController::class, 'detalleGrupo']);
    Route::get('grupos/{id}/estudiantes', [SolicitudesController::class, 'listarEstudiantesGrupo']);
    Route::get('estudiantes/disponibles', [SolicitudesController::class, 'listarEstudiantesDisponibles']);
    Route::patch('estudiantes/{id}', [SolicitudesController::class, 'actualizarEstudiante']);
    Route::patch('estudiantes-verano/{id}', [SolicitudesController::class, 'actualizarEstudianteVerano']);

    Route::get('dashboard/estudiantes', [SolicitudesController::class, 'dashboardEstudiantes']);
    Route::get('dashboard/profesores', [SolicitudesController::class, 'dashboardProfesores']);
    Route::get('dashboard/grupos', [SolicitudesController::class, 'dashboardGrupos']);

    Route::get('estudiantes/{id}', [SolicitudesController::class, 'detalleEstudiante']);
    Route::get('profesores/{id}', [SolicitudesController::class, 'detalleProfesor']);

    Route::get('reportes', [SolicitudesController::class, 'reportes']);
    Route::get('reportes/export', [SolicitudesController::class, 'exportarReportePdf']);

    Route::get('promociones/elegibles', [PromocionesController::class, 'elegibles']);
    Route::post('promociones/aplicar', [PromocionesController::class, 'aplicar']);
    Route::post('promociones/revertir', [PromocionesController::class, 'revertir']);
});

Route::middleware('auth:api')->prefix('estudiante')->group(function () {
    Route::get('notificaciones', [EstudianteNotificacionesController::class, 'index']);
    Route::patch('notificaciones/{id}/leer', [EstudianteNotificacionesController::class, 'marcarLeida']);
    Route::patch('notificaciones/leer-todas', [EstudianteNotificacionesController::class, 'marcarTodasLeidas']);
    Route::delete('notificaciones/{id}', [EstudianteNotificacionesController::class, 'eliminar']);
    Route::delete('notificaciones', [EstudianteNotificacionesController::class, 'eliminarTodas']);
    Route::patch('password', [EstudiantePasswordController::class, 'cambiar']);
});

Route::middleware('auth:api')->prefix('profesor')->group(function () {
    Route::get('curso-activo', [ProfesorCursoController::class, 'listarCursos']);
    Route::get('curso-activo/estudiantes', [ProfesorCursoController::class, 'listarEstudiantes']);
    Route::post('curso-activo/notas', [ProfesorCursoController::class, 'guardarNotas']);
    Route::patch('password', [ProfesorCursoController::class, 'cambiarPassword']);
});

Route::prefix('public')->group(function () {
    Route::post('inscripcion/ubicacion', [PruebaUbicacionController::class, 'store']);
    Route::post('abono', [AbonoController::class, 'store']);
    Route::post('inscripcion/verano', [VeranoController::class, 'store']);
    Route::post('estudiante/registro', [RegistroEstudianteController::class, 'store']);
    Route::post('estudiante/password/solicitar', [EstudiantePasswordResetController::class, 'solicitar']);
    Route::post('estudiante/password/reset', [EstudiantePasswordResetController::class, 'resetear']);
    Route::post('profesor/password/solicitar', [EstudiantePasswordResetController::class, 'solicitarProfesor']);
    Route::post('profesor/password/reset', [EstudiantePasswordResetController::class, 'resetearProfesor']);
});
