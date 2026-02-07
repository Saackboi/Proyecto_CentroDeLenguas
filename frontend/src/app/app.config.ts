import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { ApplicationConfig, isDevMode, provideBrowserGlobalErrorListeners } from '@angular/core';
import { provideNzI18n, es_ES } from 'ng-zorro-antd/i18n';
import { NzMessageService } from 'ng-zorro-antd/message';
import { provideEffects } from '@ngrx/effects';
import { provideState, provideStore } from '@ngrx/store';
import { provideStoreDevtools } from '@ngrx/store-devtools';
import { provideRouter } from '@angular/router';

import { routes } from './app.routes';
import { authInterceptor } from './core/interceptors/auth.interceptor';
import { AuthEffects } from './core/store/auth/auth.effects';
import { AUTH_FEATURE_KEY, authReducer } from './core/store/auth/auth.reducer';
import { AdminDashboardEffects } from './features/admin/dashboard/data-access/store/admin-dashboard.effects';
import {
  ADMIN_DASHBOARD_FEATURE_KEY,
  adminDashboardReducer
} from './features/admin/dashboard/data-access/store/admin-dashboard.reducer';
import { AdminLandingEffects } from './features/admin/landing/data-access/store/admin-landing.effects';
import {
  ADMIN_LANDING_FEATURE_KEY,
  adminLandingReducer
} from './features/admin/landing/data-access/store/admin-landing.reducer';
import { AdminSolicitudesEffects } from './features/admin/solicitudes/data-access/store/admin-solicitudes.effects';
import {
  ADMIN_SOLICITUDES_FEATURE_KEY,
  adminSolicitudesReducer
} from './features/admin/solicitudes/data-access/store/admin-solicitudes.reducer';

export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideRouter(routes),
    provideHttpClient(withInterceptors([authInterceptor])),
    provideNzI18n(es_ES),
    NzMessageService,
    provideStore(),
    provideStoreDevtools({
      maxAge: 50,
      logOnly: !isDevMode()
    }),
    provideState(AUTH_FEATURE_KEY, authReducer),
    provideState(ADMIN_DASHBOARD_FEATURE_KEY, adminDashboardReducer),
    provideState(ADMIN_LANDING_FEATURE_KEY, adminLandingReducer),
    provideState(ADMIN_SOLICITUDES_FEATURE_KEY, adminSolicitudesReducer),
    provideEffects(AuthEffects, AdminDashboardEffects, AdminLandingEffects, AdminSolicitudesEffects)
  ]
};
