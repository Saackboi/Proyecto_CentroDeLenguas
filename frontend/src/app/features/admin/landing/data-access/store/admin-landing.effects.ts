import { Injectable, inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { NzMessageService } from 'ng-zorro-antd/message';
import { catchError, map, of, switchMap, tap } from 'rxjs';

import { ADMIN_LANDING_ERROR_MESSAGES } from '../constants/admin-landing.const';
import { AdminLandingService } from '../services/admin-landing.service';
import { AdminLandingActions } from './admin-landing.actions';

@Injectable()
export class AdminLandingEffects {
  private readonly actions$ = inject(Actions);
  private readonly landingService = inject(AdminLandingService);
  private readonly message = inject(NzMessageService);

  loadAnnouncement$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminLandingActions.loadAnnouncement),
      switchMap(() =>
        this.landingService.getAnnouncement().pipe(
          map((announcement) =>
            AdminLandingActions.loadAnnouncementSuccess({ announcement })
          ),
          catchError(() =>
            of(
              AdminLandingActions.loadAnnouncementFailure({
                error: ADMIN_LANDING_ERROR_MESSAGES.loadAnnouncement
              })
            )
          )
        )
      )
    )
  );

  updateAnnouncement$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminLandingActions.updateAnnouncement),
      switchMap(({ statusCode }) =>
        this.landingService.updateAnnouncement(statusCode).pipe(
          map((announcement) =>
            AdminLandingActions.updateAnnouncementSuccess({ announcement })
          ),
          tap(() => this.message.success('Anuncio actualizado.')),
          catchError(() => {
            this.message.error(ADMIN_LANDING_ERROR_MESSAGES.updateAnnouncement);
            return of(
              AdminLandingActions.updateAnnouncementFailure({
                error: ADMIN_LANDING_ERROR_MESSAGES.updateAnnouncement
              })
            );
          })
        )
      )
    )
  );

  constructor() {}
}
