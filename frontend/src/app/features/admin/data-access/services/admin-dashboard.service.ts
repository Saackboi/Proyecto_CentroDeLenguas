import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, forkJoin, map } from 'rxjs';

import { environment } from '../../../../../environments/environment';
import { ApiResponseDto } from '../../../../core/models/api-response.dto';
import { ADMIN_API_PATHS } from '../constants/admin-dashboard.const';
import { DashboardCounts, DatatableResponseDto, UnknownRecord } from '../models/admin-dashboard.model';

@Injectable({ providedIn: 'root' })
export class AdminDashboardService {
  private readonly baseUrl = environment.apiBaseUrl;

  constructor(private readonly http: HttpClient) {}

  getDashboardCounts(): Observable<DashboardCounts> {
    return forkJoin({
      estudiantes: this.getDatatableCount(this.buildUrl(ADMIN_API_PATHS.dashboardEstudiantes)),
      profesores: this.getDatatableCount(this.buildUrl(ADMIN_API_PATHS.dashboardProfesores)),
      grupos: this.getDatatableCount(this.buildUrl(ADMIN_API_PATHS.dashboardGrupos)),
      solicitudes: this.getSolicitudesCount()
    });
  }

  private getDatatableCount(url: string): Observable<number> {
    const params = new HttpParams()
      .set('draw', '1')
      .set('start', '0')
      .set('length', '1');

    return this.http.get<DatatableResponseDto<UnknownRecord>>(url, { params }).pipe(
      map((response) => response.recordsTotal ?? 0)
    );
  }

  private getSolicitudesCount(): Observable<number> {
    return forkJoin({
      ubicacion: this.getSolicitudes(this.buildUrl(ADMIN_API_PATHS.solicitudesUbicacion)),
      verano: this.getSolicitudes(this.buildUrl(ADMIN_API_PATHS.solicitudesVerano)),
      abonos: this.getSolicitudes(this.buildUrl(ADMIN_API_PATHS.solicitudesAbonos))
    }).pipe(map((result) => result.ubicacion + result.verano + result.abonos));
  }

  private getSolicitudes(url: string): Observable<number> {
    return this.http.get<ApiResponseDto<UnknownRecord[]>>(url).pipe(
      map((response) => response.data?.length ?? 0)
    );
  }

  private buildUrl(path: string): string {
    return `${this.baseUrl}${path}`;
  }
}
