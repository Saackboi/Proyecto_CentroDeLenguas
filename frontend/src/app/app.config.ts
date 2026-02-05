import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { ApplicationConfig, isDevMode, provideBrowserGlobalErrorListeners } from '@angular/core';
import { provideNzI18n, es_ES } from 'ng-zorro-antd/i18n';
import { provideEffects } from '@ngrx/effects';
import { provideState, provideStore } from '@ngrx/store';
import { provideStoreDevtools } from '@ngrx/store-devtools';
import { provideRouter } from '@angular/router';

import { routes } from './app.routes';
import { authInterceptor } from './core/interceptors/auth.interceptor';
import { AdminDashboardEffects } from './features/admin/dashboard/data-access/store/admin-dashboard.effects';
import {
  ADMIN_DASHBOARD_FEATURE_KEY,
  adminDashboardReducer
} from './features/admin/dashboard/data-access/store/admin-dashboard.reducer';
import { UsersEffects } from './features/users/data-access/store/users.effects';
import { USERS_FEATURE_KEY, usersReducer } from './features/users/data-access/store/users.reducer';

export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideRouter(routes),
    provideHttpClient(withInterceptors([authInterceptor])),
    provideNzI18n(es_ES),
    provideStore(),
    provideStoreDevtools({
      maxAge: 50,
      logOnly: !isDevMode()
    }),
    provideState(ADMIN_DASHBOARD_FEATURE_KEY, adminDashboardReducer),
    provideState(USERS_FEATURE_KEY, usersReducer),
    provideEffects(AdminDashboardEffects, UsersEffects)
  ]
};
