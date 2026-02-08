# Sugerencias no prioritarias

## Solicitudes

### Flujo financiero
- Bloquear envio de abonos cuando el saldo pendiente sea 0 o menor.
- Validar en backend que el abono no exceda la deuda antes de aprobar.

### UX y feedback
- Mostrar errores inline especificos por accion (ya implementado en admin).
- Agregar indicador de estado al cargar comprobantes (placeholder/cargando).
- Incluir vista en pantalla completa para comprobantes con zoom.

### Observabilidad
- Registrar en auditoria quien aprueba/rechaza (tabla o log).
- Agregar metadata de accion (timestamp + motivo) en UI de historial.

## Estudiantes

### Validaciones de datos
- `tipo_id`: formato y longitud segun cedula/pasaporte.
- `telefono`: solo numeros, longitud minima y maxima.
- `correo_utp`: validar dominio institucional cuando aplique.
- `saldo_pendiente`: no permitir valores negativos.
- `fecha_nacimiento`: no permitir fechas futuras.
- `tipo_sangre`: validar formato (ej: O+, A-, AB+).

### UX
- Resaltar estado academico con colores segun estado.
- Mostrar alertas si el estudiante esta inactivo.
