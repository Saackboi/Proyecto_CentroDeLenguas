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

export const selectAdminSolicitudesApproveUbicacionLoading = createSelector(
  selectAdminSolicitudesState,
  (state) => state.isApprovingUbicacion
);

export const selectAdminSolicitudesRejectUbicacionLoading = createSelector(
  selectAdminSolicitudesState,
  (state) => state.isRejectingUbicacion
);

export const selectAdminSolicitudesApproveAbonoLoading = createSelector(
  selectAdminSolicitudesState,
  (state) => state.isApprovingAbono
);

export const selectAdminSolicitudesRejectAbonoLoading = createSelector(
  selectAdminSolicitudesState,
  (state) => state.isRejectingAbono
);

export const selectAdminSolicitudesApproveVeranoLoading = createSelector(
  selectAdminSolicitudesState,
  (state) => state.isApprovingVerano
);

export const selectAdminSolicitudesRejectVeranoLoading = createSelector(
  selectAdminSolicitudesState,
  (state) => state.isRejectingVerano
);

export const selectAdminSolicitudesAbonoSaldo = createSelector(
  selectAdminSolicitudesState,
  (state) => state.abonoSaldo
);

export const selectAdminSolicitudesAbonoSaldoId = createSelector(
  selectAdminSolicitudesState,
  (state) => state.abonoSaldoId
);

export const selectAdminSolicitudesAbonoSaldoLoading = createSelector(
  selectAdminSolicitudesState,
  (state) => state.isLoadingAbonoSaldo
);

export const selectAdminSolicitudesAbonoSaldoError = createSelector(
  selectAdminSolicitudesState,
  (state) => state.errorAbonoSaldo
);
