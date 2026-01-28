# Estado de migracion

## Contexto
Proyecto recreado en `ProyectoWEB_CEL_NUEVO` con stack Laravel + MySQL + JWT.
Backend en `C:\Users\Practica Profesional\Downloads\ProyectoWEB_CEL_NUEVO\backend`.

## Backend listo
- Autenticacion JWT funcionando (admin y estudiante).
- Login estudiante.
- Base de datos `celdb_v2` migrada con tablas del esquema original.
- Endpoints publicos:
  - `POST /api/public/inscripcion/ubicacion`
  - `POST /api/public/abono`
  - `POST /api/public/inscripcion/verano`
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
- Notificaciones:
  - Admin: `POST/GET/PATCH /api/admin/notificaciones`
  - Estudiante: `GET/PATCH/DELETE /api/estudiante/notificaciones`
  - Auto borrado de leidas despues de 30 dias (al listar).
- Cuentas de estudiantes:
  - Se crean automaticamente al aprobar ubicacion o verano.
  - Se envia correo con credenciales temporales (Office365 SMTP).
- Recuperacion y cambio de contrasena (estudiante):
  - `POST /api/public/estudiante/password/solicitar`
  - `POST /api/public/estudiante/password/reset`
  - `PATCH /api/estudiante/password`

## Email
- HTML adaptado con estilo de `CrearEmail.php`.
- Plantillas:
  - `resources/views/emails/estudiante_credenciales.blade.php`
  - `resources/views/emails/estudiante_credenciales_text.blade.php`

## Ajustes especiales
- En aprobacion de ubicacion, prioridad de correo: `correo_utp` si existe, si no `correo_personal`.
- Deuda/saldo base si no se pasa:
  - `es_estudiante = SI` -> 90.00
  - `es_estudiante = NO` -> 100.00

## Pendiente
- Recuperacion de contrasena para estudiantes (reset flow).
- Modulo profesor:
  - grupo activo
  - carga de notas
  - cambio de contrasena
- Reportes admin y exportacion.
- Frontend Angular + NgRx.
- Servicios/refactor del controller admin (opcional).

## Credenciales semilla
- Admin:
  - `admin@gmail.com` / `Admin12345`

## Notas de entorno
- Si el mail no envia, revisar `.env`:
  - `MAIL_MAILER=smtp`
  - limpiar cache: `php artisan config:clear`
