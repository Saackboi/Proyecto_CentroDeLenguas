import { createActionGroup, emptyProps, props } from '@ngrx/store';

import {
  SolicitudAbonoApprovalPayload,
  SolicitudAbonoView,
  SolicitudRechazoPayload,
  SolicitudUbicacionApprovalPayload,
  SolicitudUbicacionView,
  SolicitudVeranoApprovalPayload,
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
    'Load Verano Failure': props<{ error: string }>(),
    'Approve Ubicacion': props<{ payload: SolicitudUbicacionApprovalPayload }>(),
    'Approve Ubicacion Success': emptyProps(),
    'Approve Ubicacion Failure': props<{ error: string }>(),
    'Reject Ubicacion': props<{ payload: SolicitudRechazoPayload }>(),
    'Reject Ubicacion Success': emptyProps(),
    'Reject Ubicacion Failure': props<{ error: string }>(),
    'Approve Abono': props<{ payload: SolicitudAbonoApprovalPayload }>(),
    'Approve Abono Success': emptyProps(),
    'Approve Abono Failure': props<{ error: string }>(),
    'Reject Abono': props<{ payload: SolicitudRechazoPayload }>(),
    'Reject Abono Success': emptyProps(),
    'Reject Abono Failure': props<{ error: string }>(),
    'Approve Verano': props<{ payload: SolicitudVeranoApprovalPayload }>(),
    'Approve Verano Success': emptyProps(),
    'Approve Verano Failure': props<{ error: string }>(),
    'Reject Verano': props<{ payload: SolicitudRechazoPayload }>(),
    'Reject Verano Success': emptyProps(),
    'Reject Verano Failure': props<{ error: string }>(),
    'Load Abono Saldo': props<{ idEstudiante: string }>(),
    'Load Abono Saldo Success': props<{ idEstudiante: string; saldoPendiente: number }>(),
    'Load Abono Saldo Failure': props<{ error: string }>(),
    'Clear Abono Saldo': emptyProps()
  }
});
