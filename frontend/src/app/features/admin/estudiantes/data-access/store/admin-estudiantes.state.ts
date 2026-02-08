import {
  AdminEstudianteListadoView,
  EstudianteDetalleRegularView,
  EstudianteDetalleVeranoView,
  EstudianteHistorialView
} from '../models/admin-estudiantes.model';

export interface AdminEstudiantesState {
  listado: AdminEstudianteListadoView[];
  isLoadingListado: boolean;
  errorListado: string | null;
  detalleRegular: EstudianteDetalleRegularView | null;
  detalleVerano: EstudianteDetalleVeranoView | null;
  isLoadingDetalle: boolean;
  errorDetalle: string | null;
  isUpdatingRegular: boolean;
  errorUpdateRegular: string | null;
  isUpdatingVerano: boolean;
  errorUpdateVerano: string | null;
  historial: EstudianteHistorialView | null;
  isLoadingHistorial: boolean;
  errorHistorial: string | null;
}

export const initialAdminEstudiantesState: AdminEstudiantesState = {
  listado: [],
  isLoadingListado: false,
  errorListado: null,
  detalleRegular: null,
  detalleVerano: null,
  isLoadingDetalle: false,
  errorDetalle: null,
  isUpdatingRegular: false,
  errorUpdateRegular: null,
  isUpdatingVerano: false,
  errorUpdateVerano: null,
  historial: null,
  isLoadingHistorial: false,
  errorHistorial: null
};
