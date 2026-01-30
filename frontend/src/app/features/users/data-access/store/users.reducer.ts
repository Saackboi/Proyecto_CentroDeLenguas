import { createReducer, on } from '@ngrx/store';

import { UsersActions } from './users.actions';
import { initialUsersState } from './users.state';

export const USERS_FEATURE_KEY = 'users';

export const usersReducer = createReducer(
  initialUsersState,
  on(UsersActions.loadUsers, (state) => ({
    ...state,
    isLoading: true,
    error: null
  })),
  on(UsersActions.loadUsersSuccess, (state, { users }) => ({
    ...state,
    users,
    isLoading: false,
    error: null
  })),
  on(UsersActions.loadUsersFailure, (state, { error }) => ({
    ...state,
    isLoading: false,
    error
  }))
);
