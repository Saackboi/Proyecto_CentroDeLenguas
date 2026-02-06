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
  errorUbicacion: null,
  errorAbonos: null,
  errorVerano: null
};
