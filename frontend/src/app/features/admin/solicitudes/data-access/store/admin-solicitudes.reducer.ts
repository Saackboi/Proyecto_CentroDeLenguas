import { createReducer, on } from '@ngrx/store';

import { AdminSolicitudesActions } from './admin-solicitudes.actions';
import { initialAdminSolicitudesState } from './admin-solicitudes.state';

export const ADMIN_SOLICITUDES_FEATURE_KEY = 'adminSolicitudes';

export const adminSolicitudesReducer = createReducer(
  initialAdminSolicitudesState,
  on(AdminSolicitudesActions.loadUbicacion, (state) => ({
    ...state,
    isLoadingUbicacion: true,
    errorUbicacion: null
  })),
  on(AdminSolicitudesActions.loadUbicacionSuccess, (state, { ubicacion }) => ({
    ...state,
    ubicacion,
    isLoadingUbicacion: false,
    errorUbicacion: null
  })),
  on(AdminSolicitudesActions.loadUbicacionFailure, (state, { error }) => ({
    ...state,
    isLoadingUbicacion: false,
    errorUbicacion: error
  })),
  on(AdminSolicitudesActions.loadAbonos, (state) => ({
    ...state,
    isLoadingAbonos: true,
    errorAbonos: null
  })),
  on(AdminSolicitudesActions.loadAbonosSuccess, (state, { abonos }) => ({
    ...state,
    abonos,
    isLoadingAbonos: false,
    errorAbonos: null
  })),
  on(AdminSolicitudesActions.loadAbonosFailure, (state, { error }) => ({
    ...state,
    isLoadingAbonos: false,
    errorAbonos: error
  })),
  on(AdminSolicitudesActions.loadVerano, (state) => ({
    ...state,
    isLoadingVerano: true,
    errorVerano: null
  })),
  on(AdminSolicitudesActions.loadVeranoSuccess, (state, { verano }) => ({
    ...state,
    verano,
    isLoadingVerano: false,
    errorVerano: null
  })),
  on(AdminSolicitudesActions.loadVeranoFailure, (state, { error }) => ({
    ...state,
    isLoadingVerano: false,
    errorVerano: error
  }))
);
