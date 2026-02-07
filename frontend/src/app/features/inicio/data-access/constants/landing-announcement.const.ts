import { LandingAnnouncementStatus } from '../../../../shared/models/landing-announcement.model';

export const LANDING_ANNOUNCEMENT_API_PATHS = {
  announcement: '/public/landing/announcement'
} as const;

export const LANDING_ANNOUNCEMENT_FALLBACK = {
  status_code: 'aviso' as LandingAnnouncementStatus,
  title: 'Aviso Importante',
  subtitle: 'Consulta novedades antes de inscribirte.',
  updated_at: null
} as const;
