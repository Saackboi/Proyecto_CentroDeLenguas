import { createFeatureSelector, createSelector } from '@ngrx/store';

import { ADMIN_LANDING_FEATURE_KEY } from './admin-landing.reducer';
import { AdminLandingState } from './admin-landing.state';

export const selectAdminLandingState = createFeatureSelector<AdminLandingState>(
  ADMIN_LANDING_FEATURE_KEY
);

export const selectAdminLandingAnnouncement = createSelector(
  selectAdminLandingState,
  (state) => state.announcement
);

export const selectAdminLandingLoading = createSelector(
  selectAdminLandingState,
  (state) => state.isLoading
);

export const selectAdminLandingUpdating = createSelector(
  selectAdminLandingState,
  (state) => state.isUpdating
);

export const selectAdminLandingError = createSelector(
  selectAdminLandingState,
  (state) => state.error
);
