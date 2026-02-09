export type SolicitudEstadoPago = 'Pendiente' | 'Aceptado' | 'Rechazado';
export type SolicitudEstadoEstudiante = 'Activo' | 'Inactivo' | 'En proceso' | 'En prueba';
export type SolicitudEsEstudiante = 'SI' | 'NO';
export type SolicitudSexo = 'Masculino' | 'Femenino';

export interface SolicitudUbicacionDto {
  id_estudiante: string;
  id_type: string | null;
  nombre: string;
  apellido: string;
  correo_personal: string | null;
  correo_utp: string | null;
  telefono: string | null;
  fecha_registro: string | null;
  estado_estudiante: SolicitudEstadoEstudiante | null;
  tipo_pago: string | null;
  estado_pago: SolicitudEstadoPago | null;
  comprobante_imagen: string | null;
  metodo_pago: string | null;
  banco: string | null;
  propietario_cuenta: string | null;
}

export interface SolicitudUbicacionView {
  id: string;
  tipoId: string | null;
  nombre: string;
  apellido: string;
  nombreCompleto: string;
  correo: string;
  correoPersonal: string;
  correoUtp: string;
  telefono: string;
  fechaRegistro: string;
  estadoEstudiante: SolicitudEstadoEstudiante | 'Sin estado';
  estadoPago: SolicitudEstadoPago | 'Sin estado';
  comprobante: string | null;
  comprobanteUrl: string | null;
  metodoPago: string;
  banco: string;
  propietarioCuenta: string;
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
  montoRaw: number | null;
  metodoPago: string;
  banco: string;
  propietarioCuenta: string;
  comprobante: string | null;
  comprobanteUrl: string | null;
}

export interface SolicitudVeranoDto {
  id_estudiante: string;
  nombre_completo: string | null;
  celular: string | null;
  fecha_nacimiento: string | null;
  numero_casa: string | null;
  domicilio: string | null;
  sexo: SolicitudSexo | null;
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
  estado: SolicitudEstadoEstudiante | 'Sin estado';
  fechaNacimiento: string | null;
  numeroCasa: string | null;
  domicilio: string | null;
  sexo: SolicitudSexo | null;
  colegio: string | null;
  nombrePadre: string | null;
  lugarTrabajoPadre: string | null;
  telefonoTrabajoPadre: string | null;
  celularPadre: string | null;
  nombreMadre: string | null;
  lugarTrabajoMadre: string | null;
  telefonoTrabajoMadre: string | null;
  celularMadre: string | null;
  contactoNombre: string | null;
  contactoTelefono: string | null;
  tipoSangre: string | null;
  alergias: string | null;
  firmaFamiliar: string | null;
  cedulaFamiliar: string | null;
  cedulaEstudiante: string | null;
  firmaFamiliarUrl: string | null;
  cedulaFamiliarUrl: string | null;
  cedulaEstudianteUrl: string | null;
}

export interface SolicitudUbicacionApprovalPayload {
  id_estudiante: string;
  nivel: string | null;
  id_type: string;
  nombre: string;
  apellido: string;
  correo_personal: string;
  correo_utp: string | null;
  telefono: string;
  estado: 'Activo' | 'Inactivo';
  saldo_pendiente: number | null;
  es_estudiante: SolicitudEsEstudiante;
}

export interface SolicitudVeranoApprovalPayload {
  id_estudiante: string;
  estado: SolicitudEstadoEstudiante | 'En proceso';
  nivel: string;
  nombre_completo: string;
  celular: string;
  fecha_nacimiento: string;
  numero_casa: string | null;
  domicilio: string;
  sexo: SolicitudSexo;
  correo: string;
  colegio: string;
  tipo_sangre: string;
  nombre_madre: string | null;
  lugar_trabajo_madre: string | null;
  telefono_trabajo_madre: string | null;
  celular_madre: string | null;
  nombre_padre: string | null;
  lugar_trabajo_padre: string | null;
  telefono_trabajo_padre: string | null;
  celular_padre: string | null;
  alergias: string | null;
  contacto_nombre: string | null;
  contacto_telefono: string | null;
}

export interface SolicitudAbonoApprovalPayload {
  id_estudiante: string;
  saldo_pendiente: number;
  abono: number;
}

export interface SolicitudRechazoPayload {
  id_estudiante: string;
  motivo: string;
}

export interface StudentRegularDetailDto {
  id_estudiante: string;
  saldo_pendiente: number | null;
  deuda_total: number | null;
}

export interface StudentRegularDetailResponseDto {
  tipo: 'regular' | 'verano';
  data: StudentRegularDetailDto;
}

