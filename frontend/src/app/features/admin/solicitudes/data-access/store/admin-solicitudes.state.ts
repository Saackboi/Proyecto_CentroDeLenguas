import {
  SolicitudAbonoView,
  SolicitudUbicacionView,
  SolicitudVeranoView
} from '../../../data-access/models/admin-solicitudes.model';

export interface AdminSolicitudesState {
  ubicacion: SolicitudUbicacionView[];
  abonos: SolicitudAbonoView[];
  verano: SolicitudVeranoView[];
  isLoadingUbicacion: boolean;
  isLoadingAbonos: boolean;
  isLoadingVerano: boolean;
  isApprovingUbicacion: boolean;
  isRejectingUbicacion: boolean;
  isApprovingAbono: boolean;
  isRejectingAbono: boolean;
  isApprovingVerano: boolean;
  isRejectingVerano: boolean;
  isLoadingAbonoSaldo: boolean;
  abonoSaldo: number | null;
  abonoSaldoId: string | null;
  errorAbonoSaldo: string | null;
  errorUbicacion: string | null;
  errorAbonos: string | null;
  errorVerano: string | null;
}

export const initialAdminSolicitudesState: AdminSolicitudesState = {
  ubicacion: [],
  abonos: [],
  verano: [],
  isLoadingUbicacion: false,
  isLoadingAbonos: false,
  isLoadingVerano: false,
  isApprovingUbicacion: false,
  isRejectingUbicacion: false,
  isApprovingAbono: false,
  isRejectingAbono: false,
  isApprovingVerano: false,
  isRejectingVerano: false,
  isLoadingAbonoSaldo: false,
  abonoSaldo: null,
  abonoSaldoId: null,
  errorAbonoSaldo: null,
  errorUbicacion: null,
  errorAbonos: null,
  errorVerano: null
};
