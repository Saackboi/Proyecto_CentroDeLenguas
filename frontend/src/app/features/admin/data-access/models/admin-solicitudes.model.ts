export type SolicitudEstadoPago = 'Pendiente' | 'Aceptado' | 'Rechazado';

export interface SolicitudUbicacionDto {
  id_estudiante: string;
  nombre: string;
  apellido: string;
  correo_personal: string | null;
  correo_utp: string | null;
  telefono: string | null;
  fecha_registro: string | null;
  estado_pago: SolicitudEstadoPago | null;
  comprobante_imagen: string | null;
}

export interface SolicitudUbicacionView {
  id: string;
  nombreCompleto: string;
  correo: string;
  telefono: string;
  fechaRegistro: string;
  estadoPago: SolicitudEstadoPago | 'Sin estado';
  comprobante: string | null;
}

