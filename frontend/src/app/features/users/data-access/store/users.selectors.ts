import { createFeatureSelector, createSelector } from '@ngrx/store';

import { USERS_FEATURE_KEY } from './users.reducer';
import { UsersState } from './users.state';

export const selectUsersState = createFeatureSelector<UsersState>(USERS_FEATURE_KEY);

export const selectUsers = createSelector(selectUsersState, (state) => state.users);
export const selectUsersLoading = createSelector(
  selectUsersState,
  (state) => state.isLoading
);
export const selectUsersError = createSelector(selectUsersState, (state) => state.error);
