import { Injectable, inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { catchError, map, of, switchMap } from 'rxjs';

import { AdminSolicitudesService } from '../../../data-access/services/admin-solicitudes.service';
import { ADMIN_SOLICITUDES_ERROR_MESSAGES } from '../constants/admin-solicitudes.const';
import { AdminSolicitudesActions } from './admin-solicitudes.actions';

@Injectable()
export class AdminSolicitudesEffects {
  private readonly actions$ = inject(Actions);
  private readonly solicitudesService = inject(AdminSolicitudesService);

  loadUbicacion$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminSolicitudesActions.loadUbicacion),
      switchMap(() =>
        this.solicitudesService.getUbicacion().pipe(
          map((ubicacion) => AdminSolicitudesActions.loadUbicacionSuccess({ ubicacion })),
          catchError(() =>
            of(
              AdminSolicitudesActions.loadUbicacionFailure({
                error: ADMIN_SOLICITUDES_ERROR_MESSAGES.loadUbicacion
              })
            )
          )
        )
      )
    )
  );

  loadAbonos$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminSolicitudesActions.loadAbonos),
      switchMap(() =>
        this.solicitudesService.getAbonos().pipe(
          map((abonos) => AdminSolicitudesActions.loadAbonosSuccess({ abonos })),
          catchError(() =>
            of(
              AdminSolicitudesActions.loadAbonosFailure({
                error: ADMIN_SOLICITUDES_ERROR_MESSAGES.loadAbonos
              })
            )
          )
        )
      )
    )
  );

  loadVerano$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminSolicitudesActions.loadVerano),
      switchMap(() =>
        this.solicitudesService.getVerano().pipe(
          map((verano) => AdminSolicitudesActions.loadVeranoSuccess({ verano })),
          catchError(() =>
            of(
              AdminSolicitudesActions.loadVeranoFailure({
                error: ADMIN_SOLICITUDES_ERROR_MESSAGES.loadVerano
              })
            )
          )
        )
      )
    )
  );

  constructor() {}
}
