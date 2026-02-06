import { createFeatureSelector, createSelector } from '@ngrx/store';

import { ADMIN_SOLICITUDES_FEATURE_KEY } from './admin-solicitudes.reducer';
import { AdminSolicitudesState } from './admin-solicitudes.state';

export const selectAdminSolicitudesState = createFeatureSelector<AdminSolicitudesState>(
  ADMIN_SOLICITUDES_FEATURE_KEY
);

export const selectAdminSolicitudesUbicacion = createSelector(
  selectAdminSolicitudesState,
  (state) => state.ubicacion
);

export const selectAdminSolicitudesAbonos = createSelector(
  selectAdminSolicitudesState,
  (state) => state.abonos
);

export const selectAdminSolicitudesVerano = createSelector(
  selectAdminSolicitudesState,
  (state) => state.verano
);

export const selectAdminSolicitudesUbicacionLoading = createSelector(
  selectAdminSolicitudesState,
  (state) => state.isLoadingUbicacion
);

export const selectAdminSolicitudesAbonosLoading = createSelector(
  selectAdminSolicitudesState,
  (state) => state.isLoadingAbonos
);

export const selectAdminSolicitudesVeranoLoading = createSelector(
  selectAdminSolicitudesState,
  (state) => state.isLoadingVerano
);

export const selectAdminSolicitudesUbicacionError = createSelector(
  selectAdminSolicitudesState,
  (state) => state.errorUbicacion
);

export const selectAdminSolicitudesAbonosError = createSelector(
  selectAdminSolicitudesState,
  (state) => state.errorAbonos
);

export const selectAdminSolicitudesVeranoError = createSelector(
  selectAdminSolicitudesState,
  (state) => state.errorVerano
);
