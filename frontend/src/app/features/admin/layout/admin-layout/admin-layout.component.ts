import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { RouterModule } from '@angular/router';
import { Store } from '@ngrx/store';
import { map } from 'rxjs';

import { AuthActions } from '../../../../core/store/auth/auth.actions';
import { selectAuthLoading } from '../../../../core/store/auth/auth.selectors';
import { FooterComponent } from '../../../../shared/components/footer/footer.component';

@Component({
  selector: 'app-admin-layout',
  imports: [CommonModule, RouterModule, FooterComponent],
  templateUrl: './admin-layout.component.html',
  styleUrl: './admin-layout.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AdminLayoutComponent {
  private readonly store = inject(Store);

  readonly logoutState$ = this.store.select(selectAuthLoading).pipe(
    map((isLoading) => ({ status: isLoading ? ('loading' as const) : ('idle' as const) }))
  );

  onLogout(): void {
    this.store.dispatch(AuthActions.logout());
  }
}
