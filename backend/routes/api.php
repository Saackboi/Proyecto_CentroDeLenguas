<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\EstudiantesController;
use App\Http\Controllers\Api\Admin\GruposController;
use App\Http\Controllers\Api\Admin\LandingAnnouncementController as AdminLandingAnnouncementController;
use App\Http\Controllers\Api\Admin\ProfesoresController;
use App\Http\Controllers\Api\Admin\ReportesController;
use App\Http\Controllers\Api\Admin\SolicitudesAdminController;
use App\Http\Controllers\Api\Admin\PromocionesController;
use App\Http\Controllers\Api\Estudiante\PasswordController as EstudiantePasswordController;
use App\Http\Controllers\Api\Estudiante\NotificacionesController as EstudianteNotificacionesController;
use App\Http\Controllers\Api\Profesor\CursoController as ProfesorCursoController;
use App\Http\Controllers\Api\Public\AbonoController;
use App\Http\Controllers\Api\Public\EstudiantePasswordResetController;
use App\Http\Controllers\Api\Public\LandingAnnouncementController as PublicLandingAnnouncementController;
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
    Route::get('solicitudes/ubicacion', [SolicitudesAdminController::class, 'listarUbicacion']);
    Route::get('solicitudes/verano', [SolicitudesAdminController::class, 'listarVerano']);
    Route::get('solicitudes/abonos', [SolicitudesAdminController::class, 'listarAbonos']);

    Route::post('ubicacion/aprobar', [SolicitudesAdminController::class, 'aprobarUbicacion']);
    Route::post('ubicacion/rechazar', [SolicitudesAdminController::class, 'rechazarUbicacion']);

    Route::post('verano/aprobar', [SolicitudesAdminController::class, 'aprobarVerano']);
    Route::post('verano/rechazar', [SolicitudesAdminController::class, 'rechazarVerano']);

    Route::post('abono/aprobar', [SolicitudesAdminController::class, 'aprobarAbono']);
    Route::post('abono/rechazar', [SolicitudesAdminController::class, 'rechazarAbono']);

    Route::patch('landing/announcement', [AdminLandingAnnouncementController::class, 'update']);

    Route::post('profesores', [ProfesoresController::class, 'crearProfesor']);
    Route::patch('profesores/{id}', [ProfesoresController::class, 'actualizarProfesor']);
    Route::get('profesores/{id}', [ProfesoresController::class, 'detalleProfesor']);

    Route::post('grupos', [GruposController::class, 'crearGrupo']);
    Route::patch('grupos/{id}', [GruposController::class, 'actualizarGrupo']);
    Route::post('grupos/{id}/retiro/preview', [GruposController::class, 'previsualizarAjusteRetiro']);
    Route::post('grupos/{id}/retiro/confirm', [GruposController::class, 'confirmarAjusteRetiro']);
    Route::get('grupos', [GruposController::class, 'listarGrupos']);
    Route::get('grupos/{id}', [GruposController::class, 'detalleGrupo']);
    Route::get('grupos/{id}/estudiantes', [GruposController::class, 'listarEstudiantesGrupo']);
    Route::get('estudiantes/disponibles', [GruposController::class, 'listarEstudiantesDisponibles']);

    Route::patch('estudiantes/{id}', [EstudiantesController::class, 'actualizarEstudiante']);
    Route::patch('estudiantes-verano/{id}', [EstudiantesController::class, 'actualizarEstudianteVerano']);
    Route::get('estudiantes/{id}', [EstudiantesController::class, 'detalleEstudiante']);
    Route::get('estudiantes/{id}/historial', [EstudiantesController::class, 'historialFinanciero']);

    Route::get('dashboard/estudiantes', [DashboardController::class, 'dashboardEstudiantes']);
    Route::get('dashboard/profesores', [DashboardController::class, 'dashboardProfesores']);
    Route::get('dashboard/grupos', [DashboardController::class, 'dashboardGrupos']);
    Route::get('dashboard/resumen', [DashboardController::class, 'dashboardResumen']);

    Route::get('reportes', [ReportesController::class, 'reportes']);
    Route::get('reportes/export', [ReportesController::class, 'exportarReportePdf']);

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
    Route::get('landing/announcement', [PublicLandingAnnouncementController::class, 'get']);
    Route::post('inscripcion/ubicacion', [PruebaUbicacionController::class, 'store']);
    Route::post('abono', [AbonoController::class, 'store']);
    Route::post('inscripcion/verano', [VeranoController::class, 'store']);
    Route::post('estudiante/registro', [RegistroEstudianteController::class, 'store']);
    Route::post('estudiante/password/solicitar', [EstudiantePasswordResetController::class, 'solicitar']);
    Route::post('estudiante/password/reset', [EstudiantePasswordResetController::class, 'resetear']);
    Route::post('profesor/password/solicitar', [EstudiantePasswordResetController::class, 'solicitarProfesor']);
    Route::post('profesor/password/reset', [EstudiantePasswordResetController::class, 'resetearProfesor']);
});
