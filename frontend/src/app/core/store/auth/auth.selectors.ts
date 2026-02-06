import { createFeatureSelector, createSelector } from '@ngrx/store';

import { AUTH_FEATURE_KEY } from './auth.reducer';
import { AuthState } from './auth.state';

export const selectAuthState = createFeatureSelector<AuthState>(AUTH_FEATURE_KEY);

export const selectIsAuthenticated = createSelector(
  selectAuthState,
  (state) => state.isAuthenticated
);

export const selectAuthUser = createSelector(selectAuthState, (state) => state.user);

export const selectAuthRole = createSelector(selectAuthState, (state) => state.role);

export const selectAuthLoading = createSelector(selectAuthState, (state) => state.isLoading);

export const selectAuthError = createSelector(selectAuthState, (state) => state.error);
