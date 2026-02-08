import { createReducer, on } from '@ngrx/store';

import { AdminEstudiantesActions } from './admin-estudiantes.actions';
import { initialAdminEstudiantesState } from './admin-estudiantes.state';

export const ADMIN_ESTUDIANTES_FEATURE_KEY = 'adminEstudiantes';

export const adminEstudiantesReducer = createReducer(
  initialAdminEstudiantesState,
  on(AdminEstudiantesActions.loadListado, (state) => ({
    ...state,
    isLoadingListado: true,
    errorListado: null
  })),
  on(AdminEstudiantesActions.loadListadoSuccess, (state, { listado }) => ({
    ...state,
    listado,
    isLoadingListado: false,
    errorListado: null
  })),
  on(AdminEstudiantesActions.loadListadoFailure, (state, { error }) => ({
    ...state,
    isLoadingListado: false,
    errorListado: error
  })),
  on(AdminEstudiantesActions.loadDetalle, (state) => ({
    ...state,
    isLoadingDetalle: true,
    errorDetalle: null
  })),
  on(AdminEstudiantesActions.loadDetalleSuccess, (state, { regular, verano }) => ({
    ...state,
    detalleRegular: regular,
    detalleVerano: verano,
    isLoadingDetalle: false,
    errorDetalle: null
  })),
  on(AdminEstudiantesActions.loadDetalleFailure, (state, { error }) => ({
    ...state,
    isLoadingDetalle: false,
    errorDetalle: error
  })),
  on(AdminEstudiantesActions.clearDetalle, (state) => ({
    ...state,
    detalleRegular: null,
    detalleVerano: null,
    isLoadingDetalle: false,
    errorDetalle: null
  })),
  on(AdminEstudiantesActions.updateRegular, (state) => ({
    ...state,
    isUpdatingRegular: true,
    errorUpdateRegular: null
  })),
  on(AdminEstudiantesActions.updateRegularSuccess, (state) => ({
    ...state,
    isUpdatingRegular: false,
    errorUpdateRegular: null
  })),
  on(AdminEstudiantesActions.updateRegularFailure, (state, { error }) => ({
    ...state,
    isUpdatingRegular: false,
    errorUpdateRegular: error
  })),
  on(AdminEstudiantesActions.updateVerano, (state) => ({
    ...state,
    isUpdatingVerano: true,
    errorUpdateVerano: null
  })),
  on(AdminEstudiantesActions.updateVeranoSuccess, (state) => ({
    ...state,
    isUpdatingVerano: false,
    errorUpdateVerano: null
  })),
  on(AdminEstudiantesActions.updateVeranoFailure, (state, { error }) => ({
    ...state,
    isUpdatingVerano: false,
    errorUpdateVerano: error
  })),
  on(AdminEstudiantesActions.loadHistorial, (state) => ({
    ...state,
    isLoadingHistorial: true,
    errorHistorial: null
  })),
  on(AdminEstudiantesActions.loadHistorialSuccess, (state, { historial }) => ({
    ...state,
    historial,
    isLoadingHistorial: false,
    errorHistorial: null
  })),
  on(AdminEstudiantesActions.loadHistorialFailure, (state, { error }) => ({
    ...state,
    isLoadingHistorial: false,
    errorHistorial: error
  })),
  on(AdminEstudiantesActions.clearHistorial, (state) => ({
    ...state,
    historial: null,
    isLoadingHistorial: false,
    errorHistorial: null
  })),
  on(AdminEstudiantesActions.clearErrors, (state) => ({
    ...state,
    errorListado: null,
    errorDetalle: null,
    errorUpdateRegular: null,
    errorUpdateVerano: null,
    errorHistorial: null
  }))
);
