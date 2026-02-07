export type LandingAnnouncementStatus = 'abiertas' | 'cerradas' | 'proximamente' | 'aviso';

export interface LandingAnnouncementDto {
  status_code: LandingAnnouncementStatus;
  title: string;
  subtitle: string;
  updated_at: string | null;
}

export interface LandingAnnouncementView {
  statusCode: LandingAnnouncementStatus;
  title: string;
  subtitle: string;
  updatedAt: string | null;
  icon: string;
  cardClass: string;
}
