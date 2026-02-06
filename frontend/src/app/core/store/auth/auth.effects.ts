import { inject, Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Actions, createEffect, ofType, ROOT_EFFECTS_INIT } from '@ngrx/effects';
import { catchError, filter, map, of, switchMap, tap } from 'rxjs';

import { LOGIN_QUERY_PARAMS, LOGIN_STATUS } from '../../../features/auth/constants/auth-ui.const';
import { AUTH_ERROR_MESSAGES, AUTH_ROLE_ROUTES } from '../../constants/auth.const';
import { AuthService } from '../../services/auth.service';
import { AuthActions } from './auth.actions';

@Injectable()
export class AuthEffects {
  private readonly actions$ = inject(Actions);
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);

  initSession$ = createEffect(() =>
    this.actions$.pipe(
      ofType(ROOT_EFFECTS_INIT),
      filter(() => this.authService.hasToken()),
      map(() => AuthActions.loadSession())
    )
  );

  loadSession$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AuthActions.loadSession),
      switchMap(() =>
        this.authService.me().pipe(
          map((user) => AuthActions.loadSessionSuccess({ user })),
          catchError((error) => {
            this.authService.clearToken();
            return of(AuthActions.loadSessionFailure({ error: this.resolveError(error) }));
          })
        )
      )
    )
  );

  login$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AuthActions.login),
      switchMap(({ credentials }) =>
        this.authService.login(credentials).pipe(
          switchMap(() => this.authService.me()),
          switchMap((user) => {
            const target = AUTH_ROLE_ROUTES[user.role];
            const hasRoute = Boolean(
              target && this.router.config.some((route) => `/${route.path}` === target)
            );

            if (!hasRoute) {
              this.authService.clearToken();
              this.router.navigate(['/login'], {
                queryParams: { [LOGIN_QUERY_PARAMS.status]: LOGIN_STATUS.roleUnavailable }
              });
              return of(AuthActions.logoutSuccess());
            }

            this.router.navigate([target]);
            return of(AuthActions.loginSuccess({ user }));
          }),
          catchError((error) => of(AuthActions.loginFailure({ error: this.resolveError(error) })))
        )
      )
    )
  );

  logout$ = createEffect(() =>
    this.actions$.pipe(
      ofType(AuthActions.logout),
      switchMap(() => {
        const token = this.authService.getToken();
        this.authService.clearToken();
        return this.authService.logout(token ?? undefined).pipe(
          map(() => AuthActions.logoutSuccess()),
          catchError(() => of(AuthActions.logoutSuccess()))
        );
      })
    )
  );

  clearSession$ = createEffect(
    () =>
      this.actions$.pipe(
        ofType(AuthActions.logout),
        tap(() => {
          this.router.navigate(['/']);
        })
      ),
    { dispatch: false }
  );

  private resolveError(error: unknown): string {
    const message = (error as { error?: { message?: string } })?.error?.message;
    return message ?? AUTH_ERROR_MESSAGES.invalidCredentials;
  }
}
