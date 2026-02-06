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

export interface SolicitudAbonoDto {
  id_pago: number;
  id_estudiante: string;
  nombre: string;
  apellido: string;
  correo_personal: string | null;
  correo_utp: string | null;
  telefono: string | null;
  tipo_pago: string | null;
  comprobante_imagen: string | null;
  metodo_pago: string | null;
  banco: string | null;
  propietario_cuenta: string | null;
  monto: number | null;
  fecha_pago: string | null;
  estado_pago: SolicitudEstadoPago | null;
}

export interface SolicitudAbonoView {
  idPago: number;
  idEstudiante: string;
  nombreCompleto: string;
  correo: string;
  telefono: string;
  fechaPago: string;
  estadoPago: SolicitudEstadoPago | 'Sin estado';
  monto: string;
  metodoPago: string;
  banco: string;
  propietarioCuenta: string;
  comprobante: string | null;
}

export interface SolicitudVeranoDto {
  id_estudiante: string;
  nombre_completo: string | null;
  celular: string | null;
  fecha_nacimiento: string | null;
  numero_casa: string | null;
  domicilio: string | null;
  sexo: string | null;
  correo: string | null;
  colegio: string | null;
  fecha_registro: string | null;
  firma_familiar_imagen: string | null;
  cedula_familiar_imagen: string | null;
  cedula_estudiante_imagen: string | null;
  nombre_padre: string | null;
  lugar_trabajo_padre: string | null;
  telefono_trabajo_padre: string | null;
  celular_padre: string | null;
  nombre_madre: string | null;
  lugar_trabajo_madre: string | null;
  telefono_trabajo_madre: string | null;
  celular_madre: string | null;
  contacto_nombre: string | null;
  contacto_telefono: string | null;
  tipo_sangre: string | null;
  alergias: string | null;
}

export interface SolicitudVeranoView {
  id: string;
  nombreCompleto: string;
  correo: string;
  telefono: string;
  fechaRegistro: string;
  estado: 'Sin estado';
  firmaFamiliar: string | null;
  cedulaFamiliar: string | null;
  cedulaEstudiante: string | null;
}

