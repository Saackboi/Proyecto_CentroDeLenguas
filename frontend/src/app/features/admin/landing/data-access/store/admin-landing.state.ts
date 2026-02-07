import { LandingAnnouncementView } from '../../../../../shared/models/landing-announcement.model';

export interface AdminLandingState {
  announcement: LandingAnnouncementView | null;
  isLoading: boolean;
  isUpdating: boolean;
  error: string | null;
}

export const initialAdminLandingState: AdminLandingState = {
  announcement: null,
  isLoading: false,
  isUpdating: false,
  error: null
};
