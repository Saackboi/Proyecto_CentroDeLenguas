import { createReducer, on } from '@ngrx/store';

import { AuthActions } from './auth.actions';
import { initialAuthState } from './auth.state';

export const AUTH_FEATURE_KEY = 'auth';

export const authReducer = createReducer(
  initialAuthState,
  on(AuthActions.loadSession, (state) => ({
    ...state,
    isLoading: true,
    error: null
  })),
  on(AuthActions.loadSessionSuccess, (state, { user }) => ({
    ...state,
    isAuthenticated: true,
    user,
    role: user.role,
    isLoading: false,
    error: null
  })),
  on(AuthActions.loadSessionFailure, (state, { error }) => ({
    ...state,
    isAuthenticated: false,
    user: null,
    role: null,
    isLoading: false,
    error
  })),
  on(AuthActions.login, (state) => ({
    ...state,
    isLoading: true,
    error: null
  })),
  on(AuthActions.loginSuccess, (state, { user }) => ({
    ...state,
    isAuthenticated: true,
    user,
    role: user.role,
    isLoading: false,
    error: null
  })),
  on(AuthActions.loginFailure, (state, { error }) => ({
    ...state,
    isAuthenticated: false,
    user: null,
    role: null,
    isLoading: false,
    error
  })),
  on(AuthActions.logout, (state) => ({
    ...initialAuthState
  })),
  on(AuthActions.logoutSuccess, () => ({
    ...initialAuthState
  })),
  on(AuthActions.logoutFailure, (state, { error }) => ({
    ...state,
    isLoading: false,
    error
  }))
);
