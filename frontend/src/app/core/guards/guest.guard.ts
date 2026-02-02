import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { catchError, map, of } from 'rxjs';

import { AUTH_ROLE_ROUTES } from '../constants/auth.const';
import { AuthService } from '../services/auth.service';
import { LOGIN_QUERY_PARAMS, LOGIN_STATUS } from '../../features/auth/constants/auth-ui.const';

export const guestGuard: CanActivateFn = () => {
  const authService = inject(AuthService);
  const router = inject(Router);

  if (!authService.hasToken()) {
    return true;
  }

  return authService.me().pipe(
    map((user) => {
      const target = AUTH_ROLE_ROUTES[user.role];
      if (!target) {
        return router.createUrlTree(['/login'], {
          queryParams: { [LOGIN_QUERY_PARAMS.status]: LOGIN_STATUS.roleUnavailable }
        });
      }

      const exists = router.config.some((route) => `/${route.path}` === target);
      return exists
        ? router.createUrlTree([target])
        : router.createUrlTree(['/login'], {
            queryParams: { [LOGIN_QUERY_PARAMS.status]: LOGIN_STATUS.roleUnavailable }
          });
    }),
    catchError(() => {
      authService.clearToken();
      return of(true);
    })
  );
};
