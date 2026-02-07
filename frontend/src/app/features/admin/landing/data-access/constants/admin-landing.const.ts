import { LandingAnnouncementStatus } from '../../../../../shared/models/landing-announcement.model';

export const ADMIN_LANDING_API_PATHS = {
  publicAnnouncement: '/public/landing/announcement',
  updateAnnouncement: '/admin/landing/announcement'
} as const;

export const ADMIN_LANDING_ERROR_MESSAGES = {
  loadAnnouncement: 'No se pudo cargar el anuncio de la landing.',
  updateAnnouncement: 'No se pudo actualizar el anuncio de la landing.'
} as const;

export const ADMIN_LANDING_STATUS_OPTIONS: Array<{
  value: LandingAnnouncementStatus;
  label: string;
}> = [
  { value: 'abiertas', label: 'Matriculas Abiertas' },
  { value: 'cerradas', label: 'Matriculas Cerradas' },
  { value: 'proximamente', label: 'Proximo Inicio' },
  { value: 'aviso', label: 'Aviso Importante' }
];
