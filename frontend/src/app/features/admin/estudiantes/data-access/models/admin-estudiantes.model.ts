export type EstudianteTipo = 'regular' | 'verano';
export type EstudianteEstadoRegular = 'Activo' | 'Inactivo' | 'En proceso' | 'En prueba';
export type EstudianteEstadoVerano = 'Activo' | 'Inactivo' | 'En proceso';
export type EstudianteSexo = 'Masculino' | 'Femenino';

export interface AdminEstudianteListadoDto {
  id_estudiante: string;
  estudiante: string;
  nivel: string | null;
  profesor: string | null;
  estado: string;
  tipo: EstudianteTipo;
}

export interface AdminEstudianteListadoView {
  id: string;
  nombre: string;
  nivel: string;
  profesor: string;
  estado: string;
  tipo: EstudianteTipo;
  tipoLabel: string;
}

export interface EstudianteDetalleRegularDto {
  id_estudiante: string;
  id_type: string | null;
  nombre: string;
  apellido: string;
  correo_personal: string | null;
  correo_utp: string | null;
  telefono: string | null;
  nivel: string | null;
  estado: EstudianteEstadoRegular | null;
  es_estudiante: 'SI' | 'NO' | null;
  saldo_pendiente: number | null;
  deuda_total: number | null;
}

export interface EstudianteDetalleRegularView {
  id: string;
  tipoId: string;
  nombre: string;
  apellido: string;
  correoPersonal: string;
  correoUtp: string;
  telefono: string;
  nivel: string;
  estado: EstudianteEstadoRegular;
  esEstudiante: 'SI' | 'NO';
  saldoPendiente: number;
  deudaTotal: number;
}

export interface EstudianteDetalleVeranoDto {
  id_estudiante: string;
  nombre_completo: string | null;
  estado: EstudianteEstadoVerano | null;
  nivel: string | null;
  celular: string | null;
  fecha_nacimiento: string | null;
  numero_casa: string | null;
  domicilio: string | null;
  sexo: EstudianteSexo | null;
  correo: string | null;
  colegio: string | null;
  alergias: string | null;
  tipo_sangre: string | null;
  contacto_nombre: string | null;
  contacto_telefono: string | null;
  nombre_padre: string | null;
  lugar_trabajo_padre: string | null;
  telefono_trabajo_padre: string | null;
  celular_padre: string | null;
  nombre_madre: string | null;
  lugar_trabajo_madre: string | null;
  telefono_trabajo_madre: string | null;
  celular_madre: string | null;
}

export interface EstudianteDetalleVeranoView {
  id: string;
  nombreCompleto: string;
  estado: EstudianteEstadoVerano;
  nivel: string;
  celular: string;
  fechaNacimiento: string;
  numeroCasa: string;
  domicilio: string;
  sexo: EstudianteSexo | '';
  correo: string;
  colegio: string;
  alergias: string;
  tipoSangre: string;
  contactoNombre: string;
  contactoTelefono: string;
  nombrePadre: string;
  lugarTrabajoPadre: string;
  telefonoTrabajoPadre: string;
  celularPadre: string;
  nombreMadre: string;
  lugarTrabajoMadre: string;
  telefonoTrabajoMadre: string;
  celularMadre: string;
}

export interface EstudianteDetalleResponseDto {
  tipo: EstudianteTipo;
  data: EstudianteDetalleRegularDto | EstudianteDetalleVeranoDto;
}

export interface EstudianteRegularUpdatePayload {
  id_type: string;
  nombre: string;
  apellido: string;
  correo_personal: string;
  correo_utp: string | null;
  telefono: string;
  nivel: string | null;
  estado: EstudianteEstadoRegular;
  es_estudiante: 'SI' | 'NO';
  saldo_pendiente: number | null;
}

export interface EstudianteVeranoUpdatePayload {
  nivel: string | null;
  estado: EstudianteEstadoVerano;
  nombre_completo: string;
  celular: string;
  fecha_nacimiento: string;
  numero_casa: string | null;
  domicilio: string;
  sexo: EstudianteSexo;
  correo: string;
  colegio: string;
  tipo_sangre: string;
  alergias: string | null;
  contacto_nombre: string | null;
  contacto_telefono: string | null;
  nombre_madre: string | null;
  lugar_trabajo_madre: string | null;
  telefono_trabajo_madre: string | null;
  celular_madre: string | null;
  nombre_padre: string | null;
  lugar_trabajo_padre: string | null;
  telefono_trabajo_padre: string | null;
  celular_padre: string | null;
}

export interface EstudianteHistorialMovimientoDto {
  id: number;
  movement_type: 'charge' | 'payment' | 'adjustment';
  amount: number;
  reason: string;
  payment_id: number | null;
  group_session_id: number | null;
  created_at: string;
}

export interface EstudianteHistorialPagoDto {
  id_pago: number;
  payment_type: 'PruebaUbicacion' | 'Abono';
  method: string;
  bank: string | null;
  account_owner: string | null;
  receipt_path: string | null;
  amount: number;
  paid_at: string | null;
  status: 'Pendiente' | 'Aceptado' | 'Rechazado';
  created_at: string;
}

export interface EstudianteHistorialDto {
  movimientos: EstudianteHistorialMovimientoDto[];
  pagos: EstudianteHistorialPagoDto[];
}

export interface EstudianteHistorialMovimientoView {
  id: number;
  tipo: string;
  motivo: string;
  monto: string;
  fecha: string;
  referencia: string;
}

export interface EstudianteHistorialPagoView {
  id: number;
  tipo: string;
  metodo: string;
  banco: string;
  propietario: string;
  monto: string;
  fechaPago: string;
  estado: string;
  comprobante: string | null;
}

export interface EstudianteHistorialView {
  movimientos: EstudianteHistorialMovimientoView[];
  pagos: EstudianteHistorialPagoView[];
}
