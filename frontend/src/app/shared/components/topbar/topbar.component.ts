import { CommonModule } from '@angular/common';
import { Component, Input, inject } from '@angular/core';
import { RouterModule } from '@angular/router';
import { Store } from '@ngrx/store';
import { catchError, combineLatest, map, of, shareReplay, startWith, switchMap } from 'rxjs';

import { AdminNotice } from '../../../features/admin/dashboard/data-access/models/admin-notice.model';
import { AdminNoticesService } from '../../../features/admin/dashboard/data-access/services/admin-notices.service';
import { AuthService } from '../../../core/services/auth.service';
import { AuthActions } from '../../../core/store/auth/auth.actions';
import {
  selectAuthLoading,
  selectAuthRole,
  selectAuthState,
  selectIsAuthenticated
} from '../../../core/store/auth/auth.selectors';

export interface TopbarLink {
  id: string;
  label: string;
  path: string;
  fragment?: string;
}

interface NotificationsState {
  status: 'guest' | 'placeholder' | 'admin' | 'loading' | 'error';
  items: AdminNotice[];
}

@Component({
  selector: 'app-topbar',
  imports: [CommonModule, RouterModule],
  templateUrl: './topbar.component.html',
  styleUrl: './topbar.component.css'
})
export class TopbarComponent {
  @Input({ required: true }) links: TopbarLink[] = [];
  @Input() activeSection = '';

  private readonly store = inject(Store);
  private readonly authService = inject(AuthService);
  private readonly adminNoticesService = inject(AdminNoticesService);

  readonly isAuthenticated$ = this.store.select(selectIsAuthenticated);
  readonly role$ = this.store.select(selectAuthRole);
  readonly isLoading$ = this.store.select(selectAuthLoading);
  readonly authState$ = this.store.select(selectAuthState);

  readonly showAuthControls$ = this.authState$.pipe(
    map((state) => state.isAuthenticated || this.authService.hasToken()),
    startWith(this.authService.hasToken())
  );

  readonly showLogin$ = this.authState$.pipe(
    map(
      (state) =>
        !state.isAuthenticated && !state.isLoading && !this.authService.hasToken()
    )
  );

  readonly dashboardRoute$ = this.role$.pipe(
    map((role) => (role === 'Admin' || role === 'Profesor' ? '/admin' : null))
  );

  readonly notificationsState$ = combineLatest([
    this.isAuthenticated$,
    this.role$,
    this.isLoading$
  ]).pipe(
    switchMap(([isAuthenticated, role, isLoading]) => {
      if (!isAuthenticated && this.authService.hasToken()) {
        return of<NotificationsState>({ status: 'loading', items: [] });
      }
      if (!isAuthenticated) {
        return of<NotificationsState>({ status: 'guest', items: [] });
      }
      if (role !== 'Admin') {
        return of<NotificationsState>({ status: 'placeholder', items: [] });
      }

      return this.adminNoticesService.getNotices().pipe(
        map((items) => ({ status: 'admin', items } as NotificationsState)),
        catchError(() => of<NotificationsState>({ status: 'error', items: [] }))
      );
    }),
    startWith({ status: 'loading', items: [] } as NotificationsState),
    shareReplay({ bufferSize: 1, refCount: true })
  );

  readonly notificationsCount$ = this.notificationsState$.pipe(
    map((state) => state.items.length)
  );

  onLogout(): void {
    this.store.dispatch(AuthActions.logout());
  }
}
