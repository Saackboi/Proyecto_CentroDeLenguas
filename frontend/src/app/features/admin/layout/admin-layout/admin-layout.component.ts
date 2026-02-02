import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { Router, RouterModule } from '@angular/router';
import { Subject, catchError, map, of, shareReplay, startWith, switchMap, tap } from 'rxjs';

import { AuthService } from '../../../../core/services/auth.service';
import { LOGIN_QUERY_PARAMS, LOGIN_STATUS } from '../../../auth/constants/auth-ui.const';
import { FooterComponent } from '../../../../shared/components/footer/footer.component';

@Component({
  selector: 'app-admin-layout',
  imports: [CommonModule, RouterModule, FooterComponent],
  templateUrl: './admin-layout.component.html',
  styleUrl: './admin-layout.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AdminLayoutComponent {
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);
  private readonly logout$ = new Subject<void>();

  readonly logoutState$ = this.logout$.pipe(
    switchMap(() =>
      this.authService.logout().pipe(
        tap(() => this.authService.clearToken()),
        tap(() => this.router.navigate(['/login'])),
        map(() => ({ status: 'success' as const })),
        catchError(() => {
          this.authService.clearToken();
          this.router.navigate(['/login'], {
            queryParams: { [LOGIN_QUERY_PARAMS.status]: LOGIN_STATUS.logoutError }
          });
          return of({ status: 'error' as const });
        }),
        startWith({ status: 'loading' as const })
      )
    ),
    startWith({ status: 'idle' as const }),
    shareReplay({ bufferSize: 1, refCount: true })
  );

  onLogout(): void {
    this.logout$.next();
  }
}
