import { createFeatureSelector, createSelector } from '@ngrx/store';

import { ADMIN_DASHBOARD_FEATURE_KEY } from './admin-dashboard.reducer';
import { AdminDashboardState } from './admin-dashboard.state';

export const selectAdminDashboardState = createFeatureSelector<AdminDashboardState>(
  ADMIN_DASHBOARD_FEATURE_KEY
);

export const selectAdminDashboardCounts = createSelector(
  selectAdminDashboardState,
  (state) => state.counts
);

export const selectAdminDashboardCountsLoading = createSelector(
  selectAdminDashboardState,
  (state) => state.isLoadingCounts
);

export const selectAdminDashboardNotices = createSelector(
  selectAdminDashboardState,
  (state) => state.notices
);

export const selectAdminDashboardLoadingNotices = createSelector(
  selectAdminDashboardState,
  (state) => state.isLoadingNotices
);

export const selectAdminDashboardLoading = createSelector(
  selectAdminDashboardState,
  (state) => state.isLoadingCounts || state.isLoadingNotices
);

export const selectAdminDashboardCountsError = createSelector(
  selectAdminDashboardState,
  (state) => state.errorCounts
);

export const selectAdminDashboardNoticesError = createSelector(
  selectAdminDashboardState,
  (state) => state.errorNotices
);
