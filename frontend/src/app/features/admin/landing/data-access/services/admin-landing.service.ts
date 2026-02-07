import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';

import { environment } from '../../../../../../environments/environment';
import { ApiResponseDto } from '../../../../../core/models/api-response.dto';
import {
  LandingAnnouncementDto,
  LandingAnnouncementView
} from '../../../../../shared/models/landing-announcement.model';
import { ADMIN_LANDING_API_PATHS } from '../constants/admin-landing.const';
import { LANDING_ANNOUNCEMENT_STATUS_META } from '../../../../../shared/constants/landing-announcement.const';

@Injectable({ providedIn: 'root' })
export class AdminLandingService {
  private readonly baseUrl = environment.apiBaseUrl;

  constructor(private readonly http: HttpClient) {}

  getAnnouncement(): Observable<LandingAnnouncementView> {
    return this.http
      .get<ApiResponseDto<LandingAnnouncementDto>>(
        `${this.baseUrl}${ADMIN_LANDING_API_PATHS.publicAnnouncement}`
      )
      .pipe(map((response) => this.mapAnnouncement(response.data)));
  }

  updateAnnouncement(statusCode: LandingAnnouncementView['statusCode']): Observable<LandingAnnouncementView> {
    return this.http
      .patch<ApiResponseDto<LandingAnnouncementDto>>(
        `${this.baseUrl}${ADMIN_LANDING_API_PATHS.updateAnnouncement}`,
        { status_code: statusCode }
      )
      .pipe(map((response) => this.mapAnnouncement(response.data)));
  }

  private mapAnnouncement(announcement?: LandingAnnouncementDto): LandingAnnouncementView {
    const fallback: LandingAnnouncementDto = {
      status_code: 'aviso',
      title: 'Aviso Importante',
      subtitle: 'Consulta novedades antes de inscribirte.',
      updated_at: null
    };

    const data = announcement ?? fallback;
    const meta = LANDING_ANNOUNCEMENT_STATUS_META[data.status_code]
      ?? LANDING_ANNOUNCEMENT_STATUS_META.aviso;

    return {
      statusCode: data.status_code,
      title: data.title,
      subtitle: data.subtitle,
      updatedAt: data.updated_at,
      icon: meta.icon,
      cardClass: meta.cardClass
    };
  }
}
