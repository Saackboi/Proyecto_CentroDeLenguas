import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';

import { environment } from '../../../../../environments/environment';
import { ApiResponseDto } from '../../../../core/models/api-response.dto';
import { SolicitudUbicacionDto, SolicitudUbicacionView } from '../models/admin-solicitudes.model';

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
}
