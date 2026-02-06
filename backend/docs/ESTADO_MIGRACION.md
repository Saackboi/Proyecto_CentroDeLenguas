# Estado de migracion

## Contexto
Proyecto recreado en `ProyectoWEB_CEL_NUEVO` con stack Laravel + MySQL + JWT.
Backend en `C:\Users\Práctica Profesional\Documents\PP\Proyectos Apartes\Proyecto Centro de Lenguas\ProyectoWEB_CEL_NUEVO\backend`.

## Nota de estado
- Este documento tiene secciones desactualizadas del esquema antiguo.
- El backend ya se migro a esquema normalizado (tabla `users`, `people`, `students`, `teachers`, `groups`, `group_sessions`, `enrollments`, `payments`, `balance_movements`, `notifications`, `promotions`).

## Backend listo
- Autenticacion JWT funcionando (admin y estudiante).
- Login estudiante.
- Base de datos `celdb_v2` con esquema normalizado.
- Tabla: `balance_movements` para cargos/abonos/ajustes.
- Tabla: `promotions` para auditoria y revertir.
- Reportes admin con exportacion PDF.
- Promociones admin implementadas (elegibles, aplicar, revertir).
- Endpoints publicos:
  - `POST /api/public/inscripcion/ubicacion`
  - `POST /api/public/abono`
  - `POST /api/public/inscripcion/verano`
  - `POST /api/public/estudiante/registro`
- Endpoints admin:
  - Listar solicitudes:
    - `GET /api/admin/solicitudes/ubicacion`
    - `GET /api/admin/solicitudes/verano`
    - `GET /api/admin/solicitudes/abonos`
  - Aprobar/Rechazar:
    - `POST /api/admin/ubicacion/aprobar`
    - `POST /api/admin/ubicacion/rechazar`
    - `POST /api/admin/verano/aprobar`
    - `POST /api/admin/verano/rechazar`
    - `POST /api/admin/abono/aprobar`
    - `POST /api/admin/abono/rechazar`
  - Profesores:
    - `POST /api/admin/profesores`
    - `PATCH /api/admin/profesores/{id}`
    - Al crear, se envia enlace de recuperacion por correo.
  - Grupos:
    - `POST /api/admin/grupos`
    - `PATCH /api/admin/grupos/{id}`
    - `POST /api/admin/grupos/{id}/retiro/preview`
    - `POST /api/admin/grupos/{id}/retiro/confirm`
    - `GET /api/admin/grupos`
    - `GET /api/admin/grupos/{id}?tipo=regular|verano`
    - `GET /api/admin/grupos/{id}/estudiantes?tipo=regular|verano`
  - Estudiantes disponibles (grupos):
    - `GET /api/admin/estudiantes/disponibles?tipo=regular|verano&nivel=...&id_grupo=...`
  - Estudiantes (editar):
    - `PATCH /api/admin/estudiantes/{id}`
    - `PATCH /api/admin/estudiantes-verano/{id}`
  - Dashboard admin:
    - `GET /api/admin/dashboard/estudiantes`
    - `GET /api/admin/dashboard/profesores`
    - `GET /api/admin/dashboard/grupos`
    - `GET /api/admin/dashboard/resumen`
    - Soporte server-side DataTables (params draw/start/length/search/order).
  - Detalles para modales:
    - `GET /api/admin/estudiantes/{id}?tipo=regular|verano`
    - `GET /api/admin/profesores/{id}`
  - Reportes (server-side):
    - `GET /api/admin/reportes?tipo=...`
    - `GET /api/admin/reportes/export?tipo=...` (PDF)
  - Promociones (admin):
    - `GET /api/admin/promociones/elegibles?tipo=regular|verano`
    - `POST /api/admin/promociones/aplicar`
    - `POST /api/admin/promociones/revertir`
- Notificaciones (estudiante):
  - `GET/PATCH/DELETE /api/estudiante/notificaciones`
  - Auto borrado de leidas despues de 30 dias (al listar).
- Cuentas de estudiantes:
  - Se crean automaticamente al aprobar ubicacion o verano.
  - Se envia correo con credenciales temporales (Office365 SMTP).
- Recuperacion y cambio de contrasena (estudiante):
  - `POST /api/public/estudiante/password/solicitar`
  - `POST /api/public/estudiante/password/reset`
  - `PATCH /api/estudiante/password`
- Recuperacion de contrasena (profesor):
  - `POST /api/public/profesor/password/solicitar`
  - `POST /api/public/profesor/password/reset`
- Modulo profesor (API con tipo `regular|verano`):
  - `GET /api/profesor/curso-activo`
  - `GET /api/profesor/curso-activo/estudiantes`
  - `POST /api/profesor/curso-activo/notas`
  - `PATCH /api/profesor/password`

## Email
- HTML adaptado con estilo de `CrearEmail.php`.
- Plantillas:
  - `resources/views/emails/estudiante_credenciales.blade.php`
  - `resources/views/emails/estudiante_credenciales_text.blade.php`
  - `resources/views/emails/estudiante_reset_password.blade.php`
  - `resources/views/emails/estudiante_reset_password_text.blade.php`
- Reset profesor reutiliza las mismas plantillas con ruta/contexto de profesor.

## Ajustes especiales
- En aprobacion de ubicacion, prioridad de correo: `correo_utp` si existe, si no `correo_personal`.
- Deuda/saldo base si no se pasa:
  - `es_estudiante = SI` -> 90.00
  - `es_estudiante = NO` -> 100.00
- En rechazos (ubicacion, verano, abono) el motivo es obligatorio y se incluye en la notificacion.
- Saldos ahora se calculan por movimientos (cargos/abonos/ajustes) y se cachean en `saldo_pendiente`.
- Retiro de estudiantes en grupos regulares usa preview + confirm para ajustes.

## TODO (refactor AdminSolicitudes)
- Verificacion funcional realizada en entorno local con token admin.
- Aprobacion/rechazo ubicacion y verano verificados con datos seed (smoke test 200).
- Rutas admin revalidadas con route:list.

## Refactor servicios
- Controllers delgados con delegacion a servicios.
- Servicios separados: admin, profesor, estudiante, public, auth.
- Saldo/movimientos centralizados en `SaldoService`.
- Reportes/DataTables centralizados en `AdminReportService`.
- Auth en `AuthService`.
- Admin por dominio con controladores y servicios separados (dashboard/solicitudes/grupos/estudiantes/profesores/reportes).
- Abonos admin extraidos a `app/Services/AdminSolicitudes/AbonosService`.
- Ubicacion admin extraido a `app/Services/AdminSolicitudes/UbicacionService`.
- Verano admin extraido a `app/Services/AdminSolicitudes/VeranoService`.
- Profesores admin extraido a `app/Services/AdminSolicitudes/ProfesoresService`.
- Grupos admin extraido a `app/Services/AdminSolicitudes/GruposService`.
- Estudiantes admin extraido a `app/Services/AdminSolicitudes/EstudiantesService`.
- Dashboards admin extraido a `app/Services/AdminSolicitudes/DashboardsService`.
- Reportes admin extraido a `app/Services/AdminSolicitudes/ReportesService`.
- Rutas y servicios de notificaciones admin manuales eliminados (queda solo notificacion automatica por acciones).
- Servicios admin divididos por modulo en `app/Services/AdminSolicitudes` (sin cambiar endpoints).
- Estudiante en `EstudianteNotificacionesService` y `EstudiantePasswordService`.
- Profesor (cursos, notas, password) en `ProfesorCursoService`.
- Password reset en `PasswordResetService`.
- Publicos en `RegistroEstudianteService`, `VeranoService`, `PruebaUbicacionService`, `Public/AbonoService`.

## Pruebas
- Endpoints probados manualmente con Postman:
  - Autenticacion:
    - `POST /api/auth/login`
    - `POST /api/auth/logout`
    - `POST /api/auth/refresh`
    - `GET /api/auth/me`
  - Publicos:
    - `POST /api/public/inscripcion/ubicacion`
    - `POST /api/public/abono`
    - `POST /api/public/inscripcion/verano`
    - `POST /api/public/estudiante/registro`
    - `POST /api/public/estudiante/password/solicitar`
    - `POST /api/public/estudiante/password/reset`
    - `POST /api/public/profesor/password/solicitar`
    - `POST /api/public/profesor/password/reset`
  - Admin:
    - `GET /api/admin/solicitudes/ubicacion`
    - `GET /api/admin/solicitudes/verano`
    - `GET /api/admin/solicitudes/abonos`
    - `POST /api/admin/ubicacion/aprobar`
    - `POST /api/admin/ubicacion/rechazar`
    - `POST /api/admin/verano/aprobar`
    - `POST /api/admin/verano/rechazar`
    - `POST /api/admin/abono/aprobar`
    - `POST /api/admin/abono/rechazar`
    - `GET /api/admin/dashboard/estudiantes`
    - `GET /api/admin/dashboard/profesores`
    - `GET /api/admin/dashboard/grupos`
    - `GET /api/admin/reportes?tipo=nivelEstudiante`
    - `GET /api/admin/reportes?tipo=nivelEstudiante&nivel=...`
    - `GET /api/admin/reportes?tipo=statusEstudiante`
    - `GET /api/admin/reportes?tipo=statusEstudiante&estado=...`
    - `GET /api/admin/reportes?tipo=nivelProfesor`
    - `GET /api/admin/reportes?tipo=nivelProfesor&nivel=...`
  - Estudiante:
    - `GET /api/estudiante/notificaciones`
    - `PATCH /api/estudiante/notificaciones/{id}/leer`
    - `PATCH /api/estudiante/notificaciones/leer-todas`
    - `DELETE /api/estudiante/notificaciones/{id}`
    - `DELETE /api/estudiante/notificaciones`
    - `PATCH /api/estudiante/password`
  - Profesor:
    - `GET /api/profesor/curso-activo`
    - `GET /api/profesor/curso-activo/estudiantes`
    - `POST /api/profesor/curso-activo/notas`
    - `PATCH /api/profesor/password`

## Pendiente
- Frontend Angular + NgRx.

## Estado
- Backend migrado al esquema normalizado y con endpoints verificados en entorno demo.

## Ajustes recientes
- Reportes admin aceptan defaults si faltan parametros requeridos:
  - `nivelEstudiante`: usa el primer nivel disponible segun el tipo.
  - `statusEstudiante`: usa el primer estado disponible segun el tipo.
  - `nivelProfesor`: usa el primer nivel disponible en grupos.
- Respuestas API estandarizadas:
  - Exitos: `{ message, data }`.
  - Errores: `{ message, code, errors? }` (centralizado en `ApiResponse`).
- Exportar PDF requiere `barryvdh/laravel-dompdf` instalado; si no existe, devuelve `503`.

## Changelog
- 2026-02-03: AdminSolicitudes dividido por servicios (Abonos, Ubicacion, Verano, Profesores, Grupos, Estudiantes, Dashboards, Reportes).
- 2026-02-03: Helpers comunes para admin (`asegurarAdmin`, `crearNotificacion`, `crearCuentaEstudiante`, `generarIdGrupo`, `normalizarCorreo`).
- 2026-02-03: GruposService simplificado con `obtenerSesionActual` y validacion de estudiantes por tipo reutilizable.
- 2026-02-03: EstudiantesService con helpers de detalle regular/verano sin cambiar respuestas.
- 2026-02-03: ProfesoresService normaliza correo con helper comun.
- 2026-02-03: EstudiantesService normaliza correo con helper comun.
- 2026-02-03: GruposService reutiliza validaciones de tipo en helpers privados.
- 2026-02-03: Validaciones de rechazo reutilizan helper comun en ubicacion/verano/abonos.
- 2026-02-03: Rutas admin verificadas con `php artisan route:list` (sin pruebas funcionales).
- 2026-02-03: Verificacion funcional admin OK (`/api/admin/estudiantes/ST-001?tipo=regular`, `/api/admin/estudiantes/SV-001?tipo=verano`, `/api/admin/grupos/G-001?tipo=regular`, `/api/admin/grupos/GV-001?tipo=verano`, estudiantes de grupo).
- 2026-02-03: Verificacion funcional admin OK (`/api/admin/solicitudes/verano`, `/api/admin/solicitudes/abonos`).
- 2026-02-03: Abono aprobado con token admin (`/api/admin/abono/aprobar`).
- 2026-02-03: Fix crearCuentaEstudiante en UbicacionService/VeranoService (firma de helper actual).
- 2026-02-05: Se agrego endpoint resumen de dashboard admin (`/api/admin/dashboard/resumen`).
- 2026-02-05: Admin controllers separados por dominio (dashboard/solicitudes/grupos/estudiantes/profesores/reportes).
- 2026-02-06: Smoke tests admin OK (listar/aprobar/rechazar ubicacion y verano) con datos seed.
- 2026-02-06: Seeder demo ampliado con mas registros (personas, estudiantes, pagos, solicitudes de ubicacion).

## Checkpoint 2026-01-29 (resuelto)
- AdminReportService fue reescrito para el esquema normalizado (reports con `students/people`, saldos por `balance_movements`, y profesores por `group_sessions`).
- AdminNotificacionesService fue removido; no hay endpoints admin de notificaciones.
- AuthService acepta `correo/contrasena` o `email/password` para login.
- Endpoints admin de notificaciones fueron removidos (no existen en rutas actuales).
- Migracion ejecutada con `php artisan migrate:fresh` en entorno local (dev).

### Pendiente real (schema normalizado)
- Ejecutar `php artisan db:seed` para cargar los datos demo.
- Revisar contratos del frontend si algun endpoint aun devuelve columnas del esquema viejo.

## Credenciales semilla
- Admin:
  - `admin@gmail.com` / `Admin12345`

## Notas de entorno
- Si el mail no envia, revisar `.env`:
  - `MAIL_MAILER=smtp`
  - limpiar cache: `php artisan config:clear`

## Contexto operativo (para continuidad)
- El flujo admin de solicitudes ya esta implementado (listar/aprobar/rechazar).
- En rechazos (ubicacion, verano, abono) el motivo es obligatorio y se incluye en la notificacion.
- Promociones de nivel se aplican por admin masivo, no automatico por profesor.
- Regla de aprobacion: nota >= 75; para regulares, saldo pendiente = 0; verano no tiene control de pagos.
- Saldos se calculan por `balance_movements`; al aprobar abonos se registra movimiento con `id_pago`.
- Retiro de grupo regular requiere aplicar ajuste con confirmacion.

## Promociones (implementado)
- El profesor solo guarda notas; no cambia niveles.
- El admin promociona en modo manual masivo (lista de elegibles + accion de confirmacion).
- Elegibilidad aplicada:
  - Curso cerrado (ultima inscripcion por fecha_cierre).
  - nota_final >= 75.
  - Regular: saldo_pendiente <= 0.
  - Verano: sin control de pagos.
  - Nivel < 12 (nivel 12 queda como graduado/finalizado, sin promocion).
- Auditoria en tabla `promociones` (quien, cuando, desde/hacia nivel, id_grupo) con opcion de revertir.
