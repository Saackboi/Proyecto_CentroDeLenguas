en frontend: ---
**[ROL]:** Actua como un Arquitecto de Software Senior especializado en Angular Enterprise y patrones Reactivos (RxJS/NgRx).

**[OBJETIVO]:**
Estructurar y generar codigo para un Sistema Web con **Angular 18+**, **NG-ZORRO** y **NgRx (Classic)**.
El codigo debe ser **"Clean Code"**: legible, desacoplado, estrictamente tipado y escalable.

**[REGLAS DE ORO - "NO SPAGHETTI CODE"]:**
1.  **Cero Hardcoding:** URLs, claves de API, configuraciones y "magic strings" deben ir en `environment.ts` o archivos de constantes (`.const.ts`).
2.  **Tipado Estricto (DTOs):** Toda comunicacion HTTP debe usar Interfaces **DTO** (`UserResponseDto`) y mapearse a Modelos de Dominio (`User`) en el servicio/effect usando adaptadores o `RxJS map`. No uses `any`.
3.  **Smart vs. Dumb Components:**
    * **Smart (Container):** Inyecta el `Store`, hace `dispatch`, selecciona datos (`Observable$`) y usa `AsyncPipe` en el HTML. **Nunca** manipula el DOM directamente.
    * **Dumb (UI):** Solo recibe `@Input()` y emite `@Output()`. No sabe que existe el Store ni la API.
4.  **Manejo de Suscripciones:** PROHIBIDO usar `.subscribe()` dentro de los componentes. Usa **Pipes** (`| async`) en el template para gestionar la suscripcion/desuscripcion automatica.

**[ARQUITECTURA DE CARPETAS]:**
Sigue esta estructura modular estricta:

* `src/app/core/`: (Singleton)
    * `guards/`: Logica de proteccion de rutas (CanActivateFn).
    * `interceptors/`: Manejo de tokens, errores HTTP y loaders.
    * `services/`: Servicios globales (AuthService, LogService).
* `src/app/shared/`:
    * `pipes/`: Transformacion de datos visuales.
    * `components/`: Componentes UI puros reutilizables.
* `src/app/features/[NombreModulo]/`:
    * `data-access/`:
        * `store/`: Actions, Reducers, Effects, Selectors (Separados por archivo).
        * `services/`: Servicios HTTP especificos del modulo.
        * `models/`: Interfaces DTO y Modelos de UI.
    * `ui/`: Componentes de presentacion (Tablas, Tarjetas, Formularios).
    * `pages/`: Componentes contenedores (Rutas).

**[ESTANDARES TECNICOS]:**
1.  **NgRx Flow:**
    * Usa `createActionGroup` para agrupar acciones.
    * Usa `store.dispatch(Actions.nombreAccion())` para desencadenar cambios.
    * Los selectores deben ser memorizados (`createSelector`).
2.  **RxJS:**
    * Usa operadores pipeables (`switchMap`, `catchError`, `map`) en Efectos y Servicios.
    * Manejo de errores robusto en los Efectos (nunca rompas el flujo del Observable).
3.  **NG-ZORRO:**
    * Implementa los componentes de UI usando modulos de NG-ZORRO.
---

Adicionales del proyecto
- Usar comandos de Angular CLI para crear componentes, servicios, guards, modules, etc. Siempre que sea posible usar `ng generate` en lugar de creacion manual.
- El frontend usa Tailwind CSS. Centraliza estilos en archivos `.css` o `.scss` usando clases y `@apply` para evitar llenar el HTML con utilidades.
- En Effects/Components, evita inicializadores que dependan de DI via constructor. Usa `inject()` en propiedades de clase (`private readonly actions$ = inject(Actions)`, `private readonly store = inject(Store)`) para prevenir `undefined` en tiempo de construccion.
- Cuando uses `@apply` en estilos de componentes, incluye `@reference "tailwindcss"` al inicio del archivo para que el compilador reconozca las utilidades.

---

Registro de cambios
- 2026-01-31: Se agrego pagina `inicio` con landing base y estilos centralizados en `inicio-page.component.css`.
- 2026-01-31: Se actualizaron rutas para usar `InicioPageComponent` como entrada principal.
- 2026-01-31: Se añadieron fuentes/Material Icons via CDN en `src/index.html`.
- 2026-01-31: Se definieron tokens de color en `src/styles.css` con `:root`.
- 2026-01-31: Se creo `TopbarComponent` y `FooterComponent` compartidos para reutilizar el layout publico.
- 2026-01-31: Se agrego pagina `contacto` y ruta `/contacto` con estilos centralizados.
- 2026-01-31: Se implemento pagina `login` con NG-ZORRO, validaciones y redireccion temporal a `/usuarios`.
- 2026-01-31: Se implemento dashboard admin (`/admin`) con layout propio y KPI basados en endpoints reales.
- 2026-01-31: Login ahora consulta `auth/me` y redirige por rol; si no hay vista, muestra alerta "Vista no implementada aún".
- 2026-01-31: Panel de avisos recientes usa solicitudes reales y descarta via `localStorage` (clave `cel_admin_notice_dismissed`), listo para futura persistencia backend.
- 2026-01-31: Se agrego `guestGuard` para bloquear `/login` con sesion activa y logout real via `auth/logout` en admin.
- 2026-01-31: Se unificaron iconos y textos de modulos admin para consistencia visual.
- 2026-01-31: Se actualizo el texto del footer global con datos institucionales de la UTP Cocle.
- 2026-01-31: Se reemplazo el footer del admin por el footer global compartido.
- 2026-01-31: Se agrego vista `admin/solicitudes` con tabs y listado inicial de ubicacion.
- 2026-01-31: Se implemento `AdminLayout` compartido para todas las vistas admin con sidebar y footer global.
- 2026-01-31: Footer global ahora soporta modo compacto para admin.
- 2026-01-31: Se ajusto clase de gradiente en dashboard para warning de Tailwind.
- 2026-01-31: Se habilito locale es_ES para NG-ZORRO y se fijo datepicker de solicitudes.
- 2026-02-05: Admin dashboard migro a NgRx (actions/reducer/effects/selectors) y se removio estado local redundante.
- 2026-02-05: Se habilito NgRx Store Devtools en `app.config.ts` para visualizar acciones en Redux DevTools.
- 2026-02-05: Admin dashboard ahora usa endpoint resumen para contadores (una sola llamada).
- 2026-02-06: Dashboard admin agrega overlay de carga reutilizable con Ng Zorro y bloqueo de scroll durante carga.
- 2026-02-06: Admin solicitudes migro a NgRx Store (ubicacion, abonos, verano) con loading/error y filtros reactivos.
- 2026-02-06: Auth centralizado en NgRx Store (loadSession/login/logout) para Topbar reactivo.
- 2026-02-06: Topbar muestra dropdown "Mi perfil" y acceso rapido a notificaciones (admin: solicitudes).
- 2026-02-06: PublicLayout separa Topbar/Footer del router outlet (solo vistas publicas).
- 2026-02-06: Transiciones de vistas con View Transitions API (sin Angular animations).
- 2026-02-06: Logo del Topbar usa assets locales y se creo carpeta `src/assets/logos`.
- 2026-02-07: Solicitudes admin ahora incluyen modales de aprobar/rechazar/ver comprobantes, con NgRx para acciones y saldo de abonos.
- 2026-02-08: Se agrego modulo admin de estudiantes con tabs regular/verano, modales de detalle/edicion/historial y estado en NgRx. Sugerencias movidas a `frontend/docs/SUGERENCIAS.md`.
