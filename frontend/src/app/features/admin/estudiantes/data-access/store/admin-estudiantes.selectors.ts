import { createFeatureSelector, createSelector } from '@ngrx/store';

import { ADMIN_ESTUDIANTES_FEATURE_KEY } from './admin-estudiantes.reducer';
import { AdminEstudiantesState } from './admin-estudiantes.state';

export const selectAdminEstudiantesState = createFeatureSelector<AdminEstudiantesState>(
  ADMIN_ESTUDIANTES_FEATURE_KEY
);

export const selectAdminEstudiantesListado = createSelector(
  selectAdminEstudiantesState,
  (state) => state.listado
);

export const selectAdminEstudiantesListadoLoading = createSelector(
  selectAdminEstudiantesState,
  (state) => state.isLoadingListado
);

export const selectAdminEstudiantesListadoError = createSelector(
  selectAdminEstudiantesState,
  (state) => state.errorListado
);

export const selectAdminEstudiantesDetalleRegular = createSelector(
  selectAdminEstudiantesState,
  (state) => state.detalleRegular
);

export const selectAdminEstudiantesDetalleVerano = createSelector(
  selectAdminEstudiantesState,
  (state) => state.detalleVerano
);

export const selectAdminEstudiantesDetalleLoading = createSelector(
  selectAdminEstudiantesState,
  (state) => state.isLoadingDetalle
);

export const selectAdminEstudiantesDetalleError = createSelector(
  selectAdminEstudiantesState,
  (state) => state.errorDetalle
);

export const selectAdminEstudiantesUpdateRegularLoading = createSelector(
  selectAdminEstudiantesState,
  (state) => state.isUpdatingRegular
);

export const selectAdminEstudiantesUpdateRegularError = createSelector(
  selectAdminEstudiantesState,
  (state) => state.errorUpdateRegular
);

export const selectAdminEstudiantesUpdateVeranoLoading = createSelector(
  selectAdminEstudiantesState,
  (state) => state.isUpdatingVerano
);

export const selectAdminEstudiantesUpdateVeranoError = createSelector(
  selectAdminEstudiantesState,
  (state) => state.errorUpdateVerano
);

export const selectAdminEstudiantesHistorial = createSelector(
  selectAdminEstudiantesState,
  (state) => state.historial
);

export const selectAdminEstudiantesHistorialLoading = createSelector(
  selectAdminEstudiantesState,
  (state) => state.isLoadingHistorial
);

export const selectAdminEstudiantesHistorialError = createSelector(
  selectAdminEstudiantesState,
  (state) => state.errorHistorial
);
