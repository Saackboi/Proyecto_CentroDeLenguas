export const ADMIN_API_PATHS = {
  dashboardEstudiantes: '/admin/dashboard/estudiantes',
  dashboardProfesores: '/admin/dashboard/profesores',
  dashboardGrupos: '/admin/dashboard/grupos',
  solicitudesUbicacion: '/admin/solicitudes/ubicacion',
  solicitudesVerano: '/admin/solicitudes/verano',
  solicitudesAbonos: '/admin/solicitudes/abonos'
} as const;

export const ADMIN_NOTICES_STORAGE_KEY = 'cel_admin_notice_dismissed';

export const ADMIN_NOTICE_LABELS = {
  ubicacion: 'Solicitud de ubicacion',
  verano: 'Solicitud de verano',
  abono: 'Solicitud de abono'
} as const;
