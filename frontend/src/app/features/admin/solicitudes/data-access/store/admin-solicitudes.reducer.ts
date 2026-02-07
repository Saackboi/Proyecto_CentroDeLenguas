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
  })),
  on(AdminSolicitudesActions.approveUbicacion, (state) => ({
    ...state,
    isApprovingUbicacion: true
  })),
  on(AdminSolicitudesActions.approveUbicacionSuccess, (state) => ({
    ...state,
    isApprovingUbicacion: false
  })),
  on(AdminSolicitudesActions.approveUbicacionFailure, (state) => ({
    ...state,
    isApprovingUbicacion: false
  })),
  on(AdminSolicitudesActions.rejectUbicacion, (state) => ({
    ...state,
    isRejectingUbicacion: true
  })),
  on(AdminSolicitudesActions.rejectUbicacionSuccess, (state) => ({
    ...state,
    isRejectingUbicacion: false
  })),
  on(AdminSolicitudesActions.rejectUbicacionFailure, (state) => ({
    ...state,
    isRejectingUbicacion: false
  })),
  on(AdminSolicitudesActions.approveAbono, (state) => ({
    ...state,
    isApprovingAbono: true
  })),
  on(AdminSolicitudesActions.approveAbonoSuccess, (state) => ({
    ...state,
    isApprovingAbono: false
  })),
  on(AdminSolicitudesActions.approveAbonoFailure, (state) => ({
    ...state,
    isApprovingAbono: false
  })),
  on(AdminSolicitudesActions.rejectAbono, (state) => ({
    ...state,
    isRejectingAbono: true
  })),
  on(AdminSolicitudesActions.rejectAbonoSuccess, (state) => ({
    ...state,
    isRejectingAbono: false
  })),
  on(AdminSolicitudesActions.rejectAbonoFailure, (state) => ({
    ...state,
    isRejectingAbono: false
  })),
  on(AdminSolicitudesActions.approveVerano, (state) => ({
    ...state,
    isApprovingVerano: true
  })),
  on(AdminSolicitudesActions.approveVeranoSuccess, (state) => ({
    ...state,
    isApprovingVerano: false
  })),
  on(AdminSolicitudesActions.approveVeranoFailure, (state) => ({
    ...state,
    isApprovingVerano: false
  })),
  on(AdminSolicitudesActions.rejectVerano, (state) => ({
    ...state,
    isRejectingVerano: true
  })),
  on(AdminSolicitudesActions.rejectVeranoSuccess, (state) => ({
    ...state,
    isRejectingVerano: false
  })),
  on(AdminSolicitudesActions.rejectVeranoFailure, (state) => ({
    ...state,
    isRejectingVerano: false
  })),
  on(AdminSolicitudesActions.loadAbonoSaldo, (state, { idEstudiante }) => ({
    ...state,
    isLoadingAbonoSaldo: true,
    abonoSaldoId: idEstudiante,
    errorAbonoSaldo: null
  })),
  on(AdminSolicitudesActions.loadAbonoSaldoSuccess, (state, { idEstudiante, saldoPendiente }) => ({
    ...state,
    isLoadingAbonoSaldo: false,
    abonoSaldoId: idEstudiante,
    abonoSaldo: saldoPendiente,
    errorAbonoSaldo: null
  })),
  on(AdminSolicitudesActions.loadAbonoSaldoFailure, (state, { error }) => ({
    ...state,
    isLoadingAbonoSaldo: false,
    errorAbonoSaldo: error
  })),
  on(AdminSolicitudesActions.clearAbonoSaldo, (state) => ({
    ...state,
    abonoSaldo: null,
    abonoSaldoId: null,
    errorAbonoSaldo: null,
    isLoadingAbonoSaldo: false
  }))
);
