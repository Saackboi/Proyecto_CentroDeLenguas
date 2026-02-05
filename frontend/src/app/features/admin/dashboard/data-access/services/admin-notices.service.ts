import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, forkJoin, map } from 'rxjs';

import { environment } from '../../../../../../environments/environment';
import { ApiResponseDto } from '../../../../../core/models/api-response.dto';
import {
  ADMIN_API_PATHS,
  ADMIN_NOTICE_LABELS,
  ADMIN_NOTICES_STORAGE_KEY
} from '../constants/admin-dashboard.const';
import { AdminNotice, AdminNoticeType } from '../models/admin-notice.model';

interface UbicacionSolicitudDto {
  id_estudiante: string;
  nombre: string;
  apellido: string;
  fecha_registro: string | null;
}

interface VeranoSolicitudDto {
  id_estudiante: string;
  nombre_completo: string | null;
  fecha_registro: string | null;
}

interface AbonoSolicitudDto {
  id_pago: number;
  id_estudiante: string;
  nombre: string;
  apellido: string;
  fecha_pago: string | null;
}

@Injectable({ providedIn: 'root' })
export class AdminNoticesService {
  private readonly baseUrl = environment.apiBaseUrl;

  constructor(private readonly http: HttpClient) {}

  getNotices(): Observable<AdminNotice[]> {
    return forkJoin({
      ubicacion: this.getUbicacion(),
      verano: this.getVerano(),
      abonos: this.getAbonos()
    }).pipe(
      map((result) => {
        const notices = [...result.ubicacion, ...result.verano, ...result.abonos];
        const dismissed = this.getDismissedIds();

        return notices
          .filter((notice) => !dismissed.includes(notice.id))
          .sort((a, b) => b.timestamp - a.timestamp);
      })
    );
  }

  dismissNotice(id: string): void {
    const dismissed = this.getDismissedIds();
    if (!dismissed.includes(id)) {
      dismissed.push(id);
      localStorage.setItem(ADMIN_NOTICES_STORAGE_KEY, JSON.stringify(dismissed));
    }
  }

  private getUbicacion(): Observable<AdminNotice[]> {
    return this.http
      .get<ApiResponseDto<UbicacionSolicitudDto[]>>(this.buildUrl(ADMIN_API_PATHS.solicitudesUbicacion))
      .pipe(map((response) => response.data?.map((item) => this.mapUbicacion(item)) ?? []));
  }

  private getVerano(): Observable<AdminNotice[]> {
    return this.http
      .get<ApiResponseDto<VeranoSolicitudDto[]>>(this.buildUrl(ADMIN_API_PATHS.solicitudesVerano))
      .pipe(map((response) => response.data?.map((item) => this.mapVerano(item)) ?? []));
  }

  private getAbonos(): Observable<AdminNotice[]> {
    return this.http
      .get<ApiResponseDto<AbonoSolicitudDto[]>>(this.buildUrl(ADMIN_API_PATHS.solicitudesAbonos))
      .pipe(map((response) => response.data?.map((item) => this.mapAbono(item)) ?? []));
  }

  private mapUbicacion(item: UbicacionSolicitudDto): AdminNotice {
    const fullName = `${item.nombre ?? ''} ${item.apellido ?? ''}`.trim();
    const timestamp = this.parseTimestamp(item.fecha_registro);
    const timeLabel = this.formatDate(item.fecha_registro);
    return {
      id: this.buildId('ubicacion', item.id_estudiante),
      type: 'ubicacion',
      title: ADMIN_NOTICE_LABELS.ubicacion,
      subtitle: fullName || `ID ${item.id_estudiante}`,
      timeLabel,
      tooltip: `${ADMIN_NOTICE_LABELS.ubicacion} · ${fullName || item.id_estudiante} · ${timeLabel}`,
      timestamp
    };
  }

  private mapVerano(item: VeranoSolicitudDto): AdminNotice {
    const fullName = (item.nombre_completo ?? '').trim();
    const timestamp = this.parseTimestamp(item.fecha_registro);
    const timeLabel = this.formatDate(item.fecha_registro);
    return {
      id: this.buildId('verano', item.id_estudiante),
      type: 'verano',
      title: ADMIN_NOTICE_LABELS.verano,
      subtitle: fullName || `ID ${item.id_estudiante}`,
      timeLabel,
      tooltip: `${ADMIN_NOTICE_LABELS.verano} · ${fullName || item.id_estudiante} · ${timeLabel}`,
      timestamp
    };
  }

  private mapAbono(item: AbonoSolicitudDto): AdminNotice {
    const fullName = `${item.nombre ?? ''} ${item.apellido ?? ''}`.trim();
    const timestamp = this.parseTimestamp(item.fecha_pago);
    const timeLabel = this.formatDate(item.fecha_pago);
    return {
      id: this.buildId('abono', String(item.id_pago ?? item.id_estudiante)),
      type: 'abono',
      title: ADMIN_NOTICE_LABELS.abono,
      subtitle: fullName || `ID ${item.id_estudiante}`,
      timeLabel,
      tooltip: `${ADMIN_NOTICE_LABELS.abono} · ${fullName || item.id_estudiante} · ${timeLabel}`,
      timestamp
    };
  }

  private getDismissedIds(): string[] {
    const raw = localStorage.getItem(ADMIN_NOTICES_STORAGE_KEY);
    if (!raw) {
      return [];
    }

    try {
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed.filter((id) => typeof id === 'string') : [];
    } catch {
      return [];
    }
  }

  private formatDate(value: string | null): string {
    if (!value) {
      return 'Fecha pendiente';
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
      return 'Fecha pendiente';
    }

    const dateLabel = new Intl.DateTimeFormat('es-PA', {
      dateStyle: 'medium'
    }).format(date);
    const timeLabel = new Intl.DateTimeFormat('es-PA', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: false
    }).format(date);

    return `${dateLabel} ${timeLabel}`;
  }

  private parseTimestamp(value: string | null): number {
    if (!value) {
      return 0;
    }
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? 0 : date.getTime();
  }

  private buildUrl(path: string): string {
    return `${this.baseUrl}${path}`;
  }

  private buildId(type: AdminNoticeType, id: string): string {
    return `${type}:${id}`;
  }
}
