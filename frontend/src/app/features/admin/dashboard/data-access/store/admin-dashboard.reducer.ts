import { createReducer, on } from '@ngrx/store';

import { AdminDashboardActions } from './admin-dashboard.actions';
import { initialAdminDashboardState } from './admin-dashboard.state';

export const ADMIN_DASHBOARD_FEATURE_KEY = 'adminDashboard';

export const adminDashboardReducer = createReducer(
  initialAdminDashboardState,
  on(AdminDashboardActions.loadCounts, (state) => ({
    ...state,
    isLoadingCounts: true,
    errorCounts: null
  })),
  on(AdminDashboardActions.loadCountsSuccess, (state, { counts }) => ({
    ...state,
    counts,
    isLoadingCounts: false,
    errorCounts: null
  })),
  on(AdminDashboardActions.loadCountsFailure, (state, { error }) => ({
    ...state,
    isLoadingCounts: false,
    errorCounts: error
  })),
  on(AdminDashboardActions.loadNotices, (state) => ({
    ...state,
    isLoadingNotices: true,
    errorNotices: null
  })),
  on(AdminDashboardActions.loadNoticesSuccess, (state, { notices }) => ({
    ...state,
    notices,
    isLoadingNotices: false,
    errorNotices: null
  })),
  on(AdminDashboardActions.loadNoticesFailure, (state, { error }) => ({
    ...state,
    isLoadingNotices: false,
    errorNotices: error
  }))
);
