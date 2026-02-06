import { createActionGroup, emptyProps, props } from '@ngrx/store';

import {
  SolicitudAbonoView,
  SolicitudUbicacionView,
  SolicitudVeranoView
} from '../../../data-access/models/admin-solicitudes.model';

export const AdminSolicitudesActions = createActionGroup({
  source: 'Admin Solicitudes',
  events: {
    'Load Ubicacion': emptyProps(),
    'Load Ubicacion Success': props<{ ubicacion: SolicitudUbicacionView[] }>(),
    'Load Ubicacion Failure': props<{ error: string }>(),
    'Load Abonos': emptyProps(),
    'Load Abonos Success': props<{ abonos: SolicitudAbonoView[] }>(),
    'Load Abonos Failure': props<{ error: string }>(),
    'Load Verano': emptyProps(),
    'Load Verano Success': props<{ verano: SolicitudVeranoView[] }>(),
    'Load Verano Failure': props<{ error: string }>()
  }
});
