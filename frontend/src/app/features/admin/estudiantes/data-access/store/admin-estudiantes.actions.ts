import { createActionGroup, emptyProps, props } from '@ngrx/store';

import {
  AdminEstudianteListadoView,
  EstudianteDetalleRegularView,
  EstudianteDetalleVeranoView,
  EstudianteHistorialView,
  EstudianteRegularUpdatePayload,
  EstudianteTipo,
  EstudianteVeranoUpdatePayload
} from '../models/admin-estudiantes.model';

export const AdminEstudiantesActions = createActionGroup({
  source: 'Admin Estudiantes',
  events: {
    'Load Listado': emptyProps(),
    'Load Listado Success': props<{ listado: AdminEstudianteListadoView[] }>(),
    'Load Listado Failure': props<{ error: string }>(),
    'Load Detalle': props<{ id: string; tipo: EstudianteTipo }>(),
    'Load Detalle Success': props<{
      tipo: EstudianteTipo;
      regular: EstudianteDetalleRegularView | null;
      verano: EstudianteDetalleVeranoView | null;
    }>(),
    'Load Detalle Failure': props<{ error: string }>(),
    'Clear Detalle': emptyProps(),
    'Update Regular': props<{ id: string; payload: EstudianteRegularUpdatePayload }>(),
    'Update Regular Success': emptyProps(),
    'Update Regular Failure': props<{ error: string }>(),
    'Update Verano': props<{ id: string; payload: EstudianteVeranoUpdatePayload }>(),
    'Update Verano Success': emptyProps(),
    'Update Verano Failure': props<{ error: string }>(),
    'Load Historial': props<{ id: string }>(),
    'Load Historial Success': props<{ historial: EstudianteHistorialView }>(),
    'Load Historial Failure': props<{ error: string }>(),
    'Clear Historial': emptyProps(),
    'Clear Errors': emptyProps()
  }
});
