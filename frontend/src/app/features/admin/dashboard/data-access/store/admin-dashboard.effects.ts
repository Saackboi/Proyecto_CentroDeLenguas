import { Injectable, inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { catchError, map, of, switchMap } from 'rxjs';

import { ADMIN_DASHBOARD_ERROR_MESSAGES } from '../constants/admin-dashboard.const';
import { AdminDashboardService } from '../services/admin-dashboard.service';
import { AdminNoticesService } from '../services/admin-notices.service';
import { AdminDashboardActions } from './admin-dashboard.actions';

@Injectable()
export class AdminDashboardEffects {
  private readonly actions$ = inject(Actions);
  private readonly dashboardService = inject(AdminDashboardService);
  private readonly noticesService = inject(AdminNoticesService);

  loadCounts$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminDashboardActions.loadCounts),
      switchMap(() =>
        this.dashboardService.getDashboardCounts().pipe(
          map((counts) => AdminDashboardActions.loadCountsSuccess({ counts })),
          catchError(() =>
            of(
              AdminDashboardActions.loadCountsFailure({
                error: ADMIN_DASHBOARD_ERROR_MESSAGES.loadCounts
              })
            )
          )
        )
      )
    )
  );

  loadNotices$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminDashboardActions.loadNotices),
      switchMap(() =>
        this.noticesService.getNotices().pipe(
          map((notices) => AdminDashboardActions.loadNoticesSuccess({ notices })),
          catchError(() =>
            of(
              AdminDashboardActions.loadNoticesFailure({
                error: ADMIN_DASHBOARD_ERROR_MESSAGES.loadNotices
              })
            )
          )
        )
      )
    )
  );

  dismissNotice$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AdminDashboardActions.dismissNotice),
      map(({ id }) => {
        this.noticesService.dismissNotice(id);
        return AdminDashboardActions.loadNotices();
      })
    )
  );

  constructor() {}
}
