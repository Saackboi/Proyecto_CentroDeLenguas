import { LandingAnnouncementStatus } from '../models/landing-announcement.model';

export const LANDING_ANNOUNCEMENT_STATUS_META: Record<
  LandingAnnouncementStatus,
  { icon: string; cardClass: string }
> = {
  abiertas: {
    icon: 'event_available',
    cardClass: 'inicio-hero__card--open'
  },
  cerradas: {
    icon: 'event_busy',
    cardClass: 'inicio-hero__card--closed'
  },
  proximamente: {
    icon: 'schedule',
    cardClass: 'inicio-hero__card--soon'
  },
  aviso: {
    icon: 'campaign',
    cardClass: 'inicio-hero__card--notice'
  }
};
