import { Injectable, inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { NzMessageService } from 'ng-zorro-antd/message';
import { catchError, map, mergeMap, of, switchMap, tap } from 'rxjs';

import { AdminEstudiantesService } from '../services/admin-estudiantes.service';
import {
  EstudianteDetalleRegularView,
  EstudianteDetalleVeranoView
} from '../models/admin-estudiantes.model';
import { ADMIN_ESTUDIANTES_ERROR_MESSAGES } from '../constants/admin-estudiantes.const';
import { AdminEstudiantesActions } from './admin-estudiantes.actions';

@Injectable()
export class AdminEstudiantesEffects {
  private readonly actions$ = inject(Actions);
  private readonly estudiantesService = inject(AdminEstudiantesService);
  private readonly message = inject(NzMessageService);

  loadListado$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminEstudiantesActions.loadListado),
      switchMap(() =>
        this.estudiantesService.getListado().pipe(
          map((listado) => AdminEstudiantesActions.loadListadoSuccess({ listado })),
          catchError(() =>
            of(
              AdminEstudiantesActions.loadListadoFailure({
                error: ADMIN_ESTUDIANTES_ERROR_MESSAGES.loadListado
              })
            )
          )
        )
      )
    )
  );

  loadDetalle$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminEstudiantesActions.loadDetalle),
      switchMap(({ id, tipo }) =>
        this.estudiantesService.getDetalle(id, tipo).pipe(
          map((detalle) => {
            const regular = detalle.tipo === 'regular'
              ? (detalle.data as unknown as EstudianteDetalleRegularView)
              : null;
            const verano = detalle.tipo === 'verano'
              ? (detalle.data as unknown as EstudianteDetalleVeranoView)
              : null;
            return AdminEstudiantesActions.loadDetalleSuccess({
              tipo: detalle.tipo,
              regular,
              verano
            });
          }),
          catchError(() =>
            of(
              AdminEstudiantesActions.loadDetalleFailure({
                error: ADMIN_ESTUDIANTES_ERROR_MESSAGES.loadDetalle
              })
            )
          )
        )
      )
    )
  );

  updateRegular$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminEstudiantesActions.updateRegular),
      switchMap(({ id, payload }) =>
        this.estudiantesService.updateRegular(id, payload).pipe(
          tap(() => this.message.success('Estudiante actualizado.')),
          mergeMap(() => [
            AdminEstudiantesActions.updateRegularSuccess(),
            AdminEstudiantesActions.loadListado(),
            AdminEstudiantesActions.loadDetalle({ id, tipo: 'regular' })
          ]),
          catchError(() => {
            this.message.error(ADMIN_ESTUDIANTES_ERROR_MESSAGES.updateRegular);
            return of(
              AdminEstudiantesActions.updateRegularFailure({
                error: ADMIN_ESTUDIANTES_ERROR_MESSAGES.updateRegular
              })
            );
          })
        )
      )
    )
  );

  updateVerano$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminEstudiantesActions.updateVerano),
      switchMap(({ id, payload }) =>
        this.estudiantesService.updateVerano(id, payload).pipe(
          tap(() => this.message.success('Estudiante actualizado.')),
          mergeMap(() => [
            AdminEstudiantesActions.updateVeranoSuccess(),
            AdminEstudiantesActions.loadListado(),
            AdminEstudiantesActions.loadDetalle({ id, tipo: 'verano' })
          ]),
          catchError(() => {
            this.message.error(ADMIN_ESTUDIANTES_ERROR_MESSAGES.updateVerano);
            return of(
              AdminEstudiantesActions.updateVeranoFailure({
                error: ADMIN_ESTUDIANTES_ERROR_MESSAGES.updateVerano
              })
            );
          })
        )
      )
    )
  );

  loadHistorial$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminEstudiantesActions.loadHistorial),
      switchMap(({ id }) =>
        this.estudiantesService.getHistorial(id).pipe(
          map((historial) => AdminEstudiantesActions.loadHistorialSuccess({ historial })),
          catchError(() =>
            of(
              AdminEstudiantesActions.loadHistorialFailure({
                error: ADMIN_ESTUDIANTES_ERROR_MESSAGES.loadHistorial
              })
            )
          )
        )
      )
    )
  );
}
