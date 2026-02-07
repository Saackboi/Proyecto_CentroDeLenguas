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
    isApprovingUbicacion: true,
    errorApproveUbicacion: null
  })),
  on(AdminSolicitudesActions.approveUbicacionSuccess, (state) => ({
    ...state,
    isApprovingUbicacion: false,
    errorApproveUbicacion: null
  })),
  on(AdminSolicitudesActions.approveUbicacionFailure, (state, { error }) => ({
    ...state,
    isApprovingUbicacion: false,
    errorApproveUbicacion: error
  })),
  on(AdminSolicitudesActions.rejectUbicacion, (state) => ({
    ...state,
    isRejectingUbicacion: true,
    errorRejectUbicacion: null
  })),
  on(AdminSolicitudesActions.rejectUbicacionSuccess, (state) => ({
    ...state,
    isRejectingUbicacion: false,
    errorRejectUbicacion: null
  })),
  on(AdminSolicitudesActions.rejectUbicacionFailure, (state, { error }) => ({
    ...state,
    isRejectingUbicacion: false,
    errorRejectUbicacion: error
  })),
  on(AdminSolicitudesActions.clearUbicacionErrors, (state) => ({
    ...state,
    errorApproveUbicacion: null,
    errorRejectUbicacion: null
  })),
  on(AdminSolicitudesActions.approveAbono, (state) => ({
    ...state,
    isApprovingAbono: true,
    errorApproveAbono: null
  })),
  on(AdminSolicitudesActions.approveAbonoSuccess, (state) => ({
    ...state,
    isApprovingAbono: false,
    errorApproveAbono: null
  })),
  on(AdminSolicitudesActions.approveAbonoFailure, (state, { error }) => ({
    ...state,
    isApprovingAbono: false,
    errorApproveAbono: error
  })),
  on(AdminSolicitudesActions.rejectAbono, (state) => ({
    ...state,
    isRejectingAbono: true,
    errorRejectAbono: null
  })),
  on(AdminSolicitudesActions.rejectAbonoSuccess, (state) => ({
    ...state,
    isRejectingAbono: false,
    errorRejectAbono: null
  })),
  on(AdminSolicitudesActions.rejectAbonoFailure, (state, { error }) => ({
    ...state,
    isRejectingAbono: false,
    errorRejectAbono: error
  })),
  on(AdminSolicitudesActions.clearAbonoErrors, (state) => ({
    ...state,
    errorApproveAbono: null,
    errorRejectAbono: null
  })),
  on(AdminSolicitudesActions.approveVerano, (state) => ({
    ...state,
    isApprovingVerano: true,
    errorApproveVerano: null
  })),
  on(AdminSolicitudesActions.approveVeranoSuccess, (state) => ({
    ...state,
    isApprovingVerano: false,
    errorApproveVerano: null
  })),
  on(AdminSolicitudesActions.approveVeranoFailure, (state, { error }) => ({
    ...state,
    isApprovingVerano: false,
    errorApproveVerano: error
  })),
  on(AdminSolicitudesActions.rejectVerano, (state) => ({
    ...state,
    isRejectingVerano: true,
    errorRejectVerano: null
  })),
  on(AdminSolicitudesActions.rejectVeranoSuccess, (state) => ({
    ...state,
    isRejectingVerano: false,
    errorRejectVerano: null
  })),
  on(AdminSolicitudesActions.rejectVeranoFailure, (state, { error }) => ({
    ...state,
    isRejectingVerano: false,
    errorRejectVerano: error
  })),
  on(AdminSolicitudesActions.clearVeranoErrors, (state) => ({
    ...state,
    errorApproveVerano: null,
    errorRejectVerano: null
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
