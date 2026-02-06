import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';

import { environment } from '../../../../../environments/environment';
import { ApiResponseDto } from '../../../../core/models/api-response.dto';
import {
  SolicitudAbonoDto,
  SolicitudAbonoView,
  SolicitudUbicacionDto,
  SolicitudUbicacionView,
  SolicitudVeranoDto,
  SolicitudVeranoView
} from '../models/admin-solicitudes.model';

const ADMIN_SOLICITUDES_API_PATHS = {
  solicitudesUbicacion: '/admin/solicitudes/ubicacion',
  solicitudesVerano: '/admin/solicitudes/verano',
  solicitudesAbonos: '/admin/solicitudes/abonos'
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

  private mapUbicacion(item: SolicitudUbicacionDto): SolicitudUbicacionView {
    const correo = item.correo_personal || item.correo_utp || 'Sin correo';
    const telefono = item.telefono || 'Sin telefono';
    const nombreCompleto = `${item.nombre ?? ''} ${item.apellido ?? ''}`.trim();
    return {
      id: item.id_estudiante,
      nombreCompleto: nombreCompleto || `ID ${item.id_estudiante}`,
      correo,
      telefono,
      fechaRegistro: this.formatDate(item.fecha_registro),
      estadoPago: item.estado_pago || 'Sin estado',
      comprobante: item.comprobante_imagen
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
      metodoPago: item.metodo_pago || 'Sin metodo',
      banco: item.banco || 'Sin banco',
      propietarioCuenta: item.propietario_cuenta || 'Sin propietario',
      comprobante: item.comprobante_imagen
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
      estado: 'Sin estado',
      firmaFamiliar: item.firma_familiar_imagen,
      cedulaFamiliar: item.cedula_familiar_imagen,
      cedulaEstudiante: item.cedula_estudiante_imagen
    };
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
