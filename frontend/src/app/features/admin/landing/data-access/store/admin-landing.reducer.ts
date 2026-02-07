import { createReducer, on } from '@ngrx/store';

import { AdminLandingActions } from './admin-landing.actions';
import { initialAdminLandingState } from './admin-landing.state';

export const ADMIN_LANDING_FEATURE_KEY = 'adminLanding';

export const adminLandingReducer = createReducer(
  initialAdminLandingState,
  on(AdminLandingActions.loadAnnouncement, (state) => ({
    ...state,
    isLoading: true,
    error: null
  })),
  on(AdminLandingActions.loadAnnouncementSuccess, (state, { announcement }) => ({
    ...state,
    announcement,
    isLoading: false,
    error: null
  })),
  on(AdminLandingActions.loadAnnouncementFailure, (state, { error }) => ({
    ...state,
    isLoading: false,
    error
  })),
  on(AdminLandingActions.updateAnnouncement, (state) => ({
    ...state,
    isUpdating: true,
    error: null
  })),
  on(AdminLandingActions.updateAnnouncementSuccess, (state, { announcement }) => ({
    ...state,
    announcement,
    isUpdating: false,
    error: null
  })),
  on(AdminLandingActions.updateAnnouncementFailure, (state, { error }) => ({
    ...state,
    isUpdating: false,
    error
  }))
);
