import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';

import { environment } from '../../../../../../environments/environment';
import { ApiResponseDto } from '../../../../../core/models/api-response.dto';
import { ADMIN_API_PATHS } from '../constants/admin-dashboard.const';
import { DashboardCounts } from '../models/admin-dashboard.model';

@Injectable({ providedIn: 'root' })
export class AdminDashboardService {
  private readonly baseUrl = environment.apiBaseUrl;

  constructor(private readonly http: HttpClient) {}

  getDashboardCounts(): Observable<DashboardCounts> {
    return this.http
      .get<ApiResponseDto<DashboardCounts>>(this.buildUrl(ADMIN_API_PATHS.dashboardResumen))
      .pipe(
        map((response) =>
          response.data ?? {
            estudiantes: 0,
            profesores: 0,
            grupos: 0,
            solicitudes: 0
          }
        )
      );
  }

  private buildUrl(path: string): string {
    return `${this.baseUrl}${path}`;
  }
}
