import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';

import { environment } from '../../../../../environments/environment';
import { ApiResponseDto } from '../../../../core/models/api-response.dto';
import {
  LANDING_ANNOUNCEMENT_API_PATHS,
  LANDING_ANNOUNCEMENT_FALLBACK
} from '../constants/landing-announcement.const';
import { LANDING_ANNOUNCEMENT_STATUS_META } from '../../../../shared/constants/landing-announcement.const';
import {
  LandingAnnouncementDto,
  LandingAnnouncementView
} from '../../../../shared/models/landing-announcement.model';

@Injectable({ providedIn: 'root' })
export class LandingAnnouncementService {
  private readonly baseUrl = environment.apiBaseUrl;

  constructor(private readonly http: HttpClient) {}

  getAnnouncement(): Observable<LandingAnnouncementView> {
    return this.http
      .get<ApiResponseDto<LandingAnnouncementDto>>(
        `${this.baseUrl}${LANDING_ANNOUNCEMENT_API_PATHS.announcement}`
      )
      .pipe(
        map((response) => response.data ?? LANDING_ANNOUNCEMENT_FALLBACK),
        map((announcement) => this.mapAnnouncement(announcement))
      );
  }

  private mapAnnouncement(announcement: LandingAnnouncementDto): LandingAnnouncementView {
    const meta = LANDING_ANNOUNCEMENT_STATUS_META[announcement.status_code]
      ?? LANDING_ANNOUNCEMENT_STATUS_META.aviso;

    return {
      statusCode: announcement.status_code,
      title: announcement.title,
      subtitle: announcement.subtitle,
      updatedAt: announcement.updated_at,
      icon: meta.icon,
      cardClass: meta.cardClass
    };
  }
}
