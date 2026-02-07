import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';

import { environment } from '../../../../../environments/environment';
import { ApiResponseDto } from '../../../../core/models/api-response.dto';
import {
  SolicitudAbonoApprovalPayload,
  SolicitudAbonoDto,
  SolicitudAbonoView,
  SolicitudRechazoPayload,
  SolicitudUbicacionApprovalPayload,
  SolicitudUbicacionDto,
  SolicitudUbicacionView,
  SolicitudVeranoApprovalPayload,
  SolicitudVeranoDto,
  SolicitudVeranoView,
  StudentRegularDetailResponseDto
} from '../models/admin-solicitudes.model';

const ADMIN_SOLICITUDES_API_PATHS = {
  solicitudesUbicacion: '/admin/solicitudes/ubicacion',
  solicitudesVerano: '/admin/solicitudes/verano',
  solicitudesAbonos: '/admin/solicitudes/abonos',
  aprobarUbicacion: '/admin/ubicacion/aprobar',
  rechazarUbicacion: '/admin/ubicacion/rechazar',
  aprobarVerano: '/admin/verano/aprobar',
  rechazarVerano: '/admin/verano/rechazar',
  aprobarAbono: '/admin/abono/aprobar',
  rechazarAbono: '/admin/abono/rechazar',
  estudianteDetalle: '/admin/estudiantes'
} as const;

@Injectable({ providedIn: 'root' })
export class AdminSolicitudesService {
  private readonly baseUrl = environment.apiBaseUrl;

  constructor(private readonly http: HttpClient) {}

  getUbicacion(): Observable<SolicitudUbicacionView[]> {
    return this.http
      .get<ApiResponseDto<SolicitudUbicacionDto[]>>(
        `${this.baseUrl}${ADMIN_SOLICITUDES_API_PATHS.solicitudesUbicacion}`
      )
      .pipe(
        map((response) => response.data ?? []),
        map((items) => items.map((item) => this.mapUbicacion(item)))
      );
  }

  getAbonos(): Observable<SolicitudAbonoView[]> {
    return this.http
      .get<ApiResponseDto<SolicitudAbonoDto[]>>(
        `${this.baseUrl}${ADMIN_SOLICITUDES_API_PATHS.solicitudesAbonos}`
      )
      .pipe(
        map((response) => response.data ?? []),
        map((items) => items.map((item) => this.mapAbono(item)))
      );
  }

  getVerano(): Observable<SolicitudVeranoView[]> {
    return this.http
      .get<ApiResponseDto<SolicitudVeranoDto[]>>(
        `${this.baseUrl}${ADMIN_SOLICITUDES_API_PATHS.solicitudesVerano}`
      )
      .pipe(
        map((response) => response.data ?? []),
        map((items) => items.map((item) => this.mapVerano(item)))
      );
  }

  approveUbicacion(payload: SolicitudUbicacionApprovalPayload): Observable<void> {
    return this.http
      .post<ApiResponseDto<unknown>>(
        `${this.baseUrl}${ADMIN_SOLICITUDES_API_PATHS.aprobarUbicacion}`,
        payload
      )
      .pipe(map(() => undefined));
  }

  rejectUbicacion(payload: SolicitudRechazoPayload): Observable<void> {
    return this.http
      .post<ApiResponseDto<unknown>>(
        `${this.baseUrl}${ADMIN_SOLICITUDES_API_PATHS.rechazarUbicacion}`,
        payload
      )
      .pipe(map(() => undefined));
  }

  approveVerano(payload: SolicitudVeranoApprovalPayload): Observable<void> {
    return this.http
      .post<ApiResponseDto<unknown>>(
        `${this.baseUrl}${ADMIN_SOLICITUDES_API_PATHS.aprobarVerano}`,
        payload
      )
      .pipe(map(() => undefined));
  }

  rejectVerano(payload: SolicitudRechazoPayload): Observable<void> {
    return this.http
      .post<ApiResponseDto<unknown>>(
        `${this.baseUrl}${ADMIN_SOLICITUDES_API_PATHS.rechazarVerano}`,
        payload
      )
      .pipe(map(() => undefined));
  }

  approveAbono(payload: SolicitudAbonoApprovalPayload): Observable<void> {
    return this.http
      .post<ApiResponseDto<unknown>>(
        `${this.baseUrl}${ADMIN_SOLICITUDES_API_PATHS.aprobarAbono}`,
        payload
      )
      .pipe(map(() => undefined));
  }

  rejectAbono(payload: SolicitudRechazoPayload): Observable<void> {
    return this.http
      .post<ApiResponseDto<unknown>>(
        `${this.baseUrl}${ADMIN_SOLICITUDES_API_PATHS.rechazarAbono}`,
        payload
      )
      .pipe(map(() => undefined));
  }

  getStudentRegularDetail(id: string): Observable<number> {
    return this.http
      .get<ApiResponseDto<StudentRegularDetailResponseDto>>(
        `${this.baseUrl}${ADMIN_SOLICITUDES_API_PATHS.estudianteDetalle}/${id}?tipo=regular`
      )
      .pipe(map((response) => response.data?.data?.saldo_pendiente ?? 0));
  }

  private mapUbicacion(item: SolicitudUbicacionDto): SolicitudUbicacionView {
    const correo = item.correo_personal || item.correo_utp || 'Sin correo';
    const telefono = item.telefono || 'Sin telefono';
    const nombreCompleto = `${item.nombre ?? ''} ${item.apellido ?? ''}`.trim();
    return {
      id: item.id_estudiante,
      tipoId: item.tipo_id,
      nombre: item.nombre ?? '',
      apellido: item.apellido ?? '',
      nombreCompleto: nombreCompleto || `ID ${item.id_estudiante}`,
      correo,
      correoPersonal: item.correo_personal ?? '',
      correoUtp: item.correo_utp ?? '',
      telefono,
      fechaRegistro: this.formatDate(item.fecha_registro),
      estadoEstudiante: item.estado_estudiante || 'Sin estado',
      estadoPago: item.estado_pago || 'Sin estado',
      comprobante: item.comprobante_imagen,
      comprobanteUrl: this.resolveAssetUrl(item.comprobante_imagen),
      metodoPago: item.metodo_pago || 'Sin metodo',
      banco: item.banco || 'Sin banco',
      propietarioCuenta: item.propietario_cuenta || 'Sin propietario'
    };
  }

  private mapAbono(item: SolicitudAbonoDto): SolicitudAbonoView {
    const correo = item.correo_personal || item.correo_utp || 'Sin correo';
    const telefono = item.telefono || 'Sin telefono';
    const nombreCompleto = `${item.nombre ?? ''} ${item.apellido ?? ''}`.trim();
    const monto = this.formatMoney(item.monto);
    return {
      idPago: item.id_pago,
      idEstudiante: item.id_estudiante,
      nombreCompleto: nombreCompleto || `ID ${item.id_estudiante}`,
      correo,
      telefono,
      fechaPago: this.formatDate(item.fecha_pago),
      estadoPago: item.estado_pago || 'Sin estado',
      monto,
      montoRaw: item.monto ?? null,
      metodoPago: item.metodo_pago || 'Sin metodo',
      banco: item.banco || 'Sin banco',
      propietarioCuenta: item.propietario_cuenta || 'Sin propietario',
      comprobante: item.comprobante_imagen,
      comprobanteUrl: this.resolveAssetUrl(item.comprobante_imagen)
    };
  }

  private mapVerano(item: SolicitudVeranoDto): SolicitudVeranoView {
    const correo = item.correo || 'Sin correo';
    const telefono = item.celular || 'Sin telefono';
    const nombreCompleto = (item.nombre_completo ?? '').trim();
    return {
      id: item.id_estudiante,
      nombreCompleto: nombreCompleto || `ID ${item.id_estudiante}`,
      correo,
      telefono,
      fechaRegistro: this.formatDate(item.fecha_registro),
      estado: 'En proceso',
      fechaNacimiento: item.fecha_nacimiento ?? null,
      numeroCasa: item.numero_casa ?? null,
      domicilio: item.domicilio ?? null,
      sexo: item.sexo ?? null,
      colegio: item.colegio ?? null,
      nombrePadre: item.nombre_padre ?? null,
      lugarTrabajoPadre: item.lugar_trabajo_padre ?? null,
      telefonoTrabajoPadre: item.telefono_trabajo_padre ?? null,
      celularPadre: item.celular_padre ?? null,
      nombreMadre: item.nombre_madre ?? null,
      lugarTrabajoMadre: item.lugar_trabajo_madre ?? null,
      telefonoTrabajoMadre: item.telefono_trabajo_madre ?? null,
      celularMadre: item.celular_madre ?? null,
      contactoNombre: item.contacto_nombre ?? null,
      contactoTelefono: item.contacto_telefono ?? null,
      tipoSangre: item.tipo_sangre ?? null,
      alergias: item.alergias ?? null,
      firmaFamiliar: item.firma_familiar_imagen,
      cedulaFamiliar: item.cedula_familiar_imagen,
      cedulaEstudiante: item.cedula_estudiante_imagen,
      firmaFamiliarUrl: this.resolveAssetUrl(item.firma_familiar_imagen),
      cedulaFamiliarUrl: this.resolveAssetUrl(item.cedula_familiar_imagen),
      cedulaEstudianteUrl: this.resolveAssetUrl(item.cedula_estudiante_imagen)
    };
  }

  private resolveAssetUrl(path: string | null): string | null {
    if (!path) {
      return null;
    }
    if (path.startsWith('http://') || path.startsWith('https://')) {
      return path;
    }
    const base = this.baseUrl.replace(/\/api\/?$/, '');
    if (path.startsWith('/')) {
      return `${base}${path}`;
    }
    return `${base}/${path}`;
  }

  private formatDate(value: string | null): string {
    if (!value) {
      return 'Fecha pendiente';
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
      return 'Fecha pendiente';
    }
    return new Intl.DateTimeFormat('es-PA', {
      dateStyle: 'medium'
    }).format(date);
  }

  private formatMoney(value: number | null): string {
    if (value === null || Number.isNaN(value)) {
      return 'Monto pendiente';
    }
    return new Intl.NumberFormat('es-PA', {
      style: 'currency',
      currency: 'PAB'
    }).format(value);
  }
}
