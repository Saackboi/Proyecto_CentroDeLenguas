import { Injectable, inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { NzMessageService } from 'ng-zorro-antd/message';
import { catchError, map, mergeMap, of, switchMap, tap } from 'rxjs';

import { AdminSolicitudesService } from '../../../data-access/services/admin-solicitudes.service';
import { ADMIN_SOLICITUDES_ERROR_MESSAGES } from '../constants/admin-solicitudes.const';
import { AdminSolicitudesActions } from './admin-solicitudes.actions';

@Injectable()
export class AdminSolicitudesEffects {
  private readonly actions$ = inject(Actions);
  private readonly solicitudesService = inject(AdminSolicitudesService);
  private readonly message = inject(NzMessageService);

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

  loadAbonoSaldo$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminSolicitudesActions.loadAbonoSaldo),
      switchMap(({ idEstudiante }) =>
        this.solicitudesService.getStudentRegularDetail(idEstudiante).pipe(
          map((saldoPendiente) =>
            AdminSolicitudesActions.loadAbonoSaldoSuccess({ idEstudiante, saldoPendiente })
          ),
          catchError(() =>
            of(
              AdminSolicitudesActions.loadAbonoSaldoFailure({
                error: ADMIN_SOLICITUDES_ERROR_MESSAGES.loadAbonoSaldo
              })
            )
          )
        )
      )
    )
  );

  approveUbicacion$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminSolicitudesActions.approveUbicacion),
      switchMap(({ payload }) =>
        this.solicitudesService.approveUbicacion(payload).pipe(
          tap(() => this.message.success('Solicitud de ubicacion aprobada.')),
          mergeMap(() => [
            AdminSolicitudesActions.approveUbicacionSuccess(),
            AdminSolicitudesActions.loadUbicacion()
          ]),
          catchError(() => {
            this.message.error(ADMIN_SOLICITUDES_ERROR_MESSAGES.approveUbicacion);
            return of(
              AdminSolicitudesActions.approveUbicacionFailure({
                error: ADMIN_SOLICITUDES_ERROR_MESSAGES.approveUbicacion
              })
            );
          })
        )
      )
    )
  );

  rejectUbicacion$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminSolicitudesActions.rejectUbicacion),
      switchMap(({ payload }) =>
        this.solicitudesService.rejectUbicacion(payload).pipe(
          tap(() => this.message.success('Solicitud de ubicacion rechazada.')),
          mergeMap(() => [
            AdminSolicitudesActions.rejectUbicacionSuccess(),
            AdminSolicitudesActions.loadUbicacion()
          ]),
          catchError(() => {
            this.message.error(ADMIN_SOLICITUDES_ERROR_MESSAGES.rejectUbicacion);
            return of(
              AdminSolicitudesActions.rejectUbicacionFailure({
                error: ADMIN_SOLICITUDES_ERROR_MESSAGES.rejectUbicacion
              })
            );
          })
        )
      )
    )
  );

  approveAbono$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminSolicitudesActions.approveAbono),
      switchMap(({ payload }) =>
        this.solicitudesService.approveAbono(payload).pipe(
          tap(() => this.message.success('Abono aprobado.')),
          mergeMap(() => [
            AdminSolicitudesActions.approveAbonoSuccess(),
            AdminSolicitudesActions.loadAbonos()
          ]),
          catchError(() => {
            this.message.error(ADMIN_SOLICITUDES_ERROR_MESSAGES.approveAbono);
            return of(
              AdminSolicitudesActions.approveAbonoFailure({
                error: ADMIN_SOLICITUDES_ERROR_MESSAGES.approveAbono
              })
            );
          })
        )
      )
    )
  );

  rejectAbono$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminSolicitudesActions.rejectAbono),
      switchMap(({ payload }) =>
        this.solicitudesService.rejectAbono(payload).pipe(
          tap(() => this.message.success('Abono rechazado.')),
          mergeMap(() => [
            AdminSolicitudesActions.rejectAbonoSuccess(),
            AdminSolicitudesActions.loadAbonos()
          ]),
          catchError(() => {
            this.message.error(ADMIN_SOLICITUDES_ERROR_MESSAGES.rejectAbono);
            return of(
              AdminSolicitudesActions.rejectAbonoFailure({
                error: ADMIN_SOLICITUDES_ERROR_MESSAGES.rejectAbono
              })
            );
          })
        )
      )
    )
  );

  approveVerano$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminSolicitudesActions.approveVerano),
      switchMap(({ payload }) =>
        this.solicitudesService.approveVerano(payload).pipe(
          tap(() => this.message.success('Solicitud de verano aprobada.')),
          mergeMap(() => [
            AdminSolicitudesActions.approveVeranoSuccess(),
            AdminSolicitudesActions.loadVerano()
          ]),
          catchError(() => {
            this.message.error(ADMIN_SOLICITUDES_ERROR_MESSAGES.approveVerano);
            return of(
              AdminSolicitudesActions.approveVeranoFailure({
                error: ADMIN_SOLICITUDES_ERROR_MESSAGES.approveVerano
              })
            );
          })
        )
      )
    )
  );

  rejectVerano$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminSolicitudesActions.rejectVerano),
      switchMap(({ payload }) =>
        this.solicitudesService.rejectVerano(payload).pipe(
          tap(() => this.message.success('Solicitud de verano rechazada.')),
          mergeMap(() => [
            AdminSolicitudesActions.rejectVeranoSuccess(),
            AdminSolicitudesActions.loadVerano()
          ]),
          catchError(() => {
            this.message.error(ADMIN_SOLICITUDES_ERROR_MESSAGES.rejectVerano);
            return of(
              AdminSolicitudesActions.rejectVeranoFailure({
                error: ADMIN_SOLICITUDES_ERROR_MESSAGES.rejectVerano
              })
            );
          })
        )
      )
    )
  );

  constructor() {}
}
