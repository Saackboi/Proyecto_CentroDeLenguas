import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { ApplicationConfig, provideBrowserGlobalErrorListeners } from '@angular/core';
import { provideNzI18n, es_ES } from 'ng-zorro-antd/i18n';
import { provideEffects } from '@ngrx/effects';
import { provideState, provideStore } from '@ngrx/store';
import { provideRouter } from '@angular/router';

import { routes } from './app.routes';
import { authInterceptor } from './core/interceptors/auth.interceptor';
import { UsersEffects } from './features/users/data-access/store/users.effects';
import { USERS_FEATURE_KEY, usersReducer } from './features/users/data-access/store/users.reducer';

export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideRouter(routes),
    provideHttpClient(withInterceptors([authInterceptor])),
    provideNzI18n(es_ES),
    provideStore(),
    provideState(USERS_FEATURE_KEY, usersReducer),
    provideEffects(UsersEffects)
  ]
};
