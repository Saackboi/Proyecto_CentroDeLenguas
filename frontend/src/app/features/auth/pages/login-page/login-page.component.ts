import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, OnDestroy, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, ParamMap, Router, RouterModule } from '@angular/router';
import { Subject, catchError, filter, map, of, shareReplay, startWith, switchMap, tap } from 'rxjs';

import { NzAlertModule } from 'ng-zorro-antd/alert';
import { NzButtonModule } from 'ng-zorro-antd/button';
import { NzFormModule } from 'ng-zorro-antd/form';
import { NzInputModule } from 'ng-zorro-antd/input';
import { AUTH_ROLE_ROUTES } from '../../../../core/constants/auth.const';
import { AuthService } from '../../../../core/services/auth.service';
import { FooterComponent } from '../../../../shared/components/footer/footer.component';
import { TopbarComponent, TopbarLink } from '../../../../shared/components/topbar/topbar.component';
import {
  LOGIN_ERROR_MESSAGES,
  LOGIN_QUERY_PARAMS,
  LOGIN_STATUS,
  LOGIN_STATUS_MESSAGES
} from '../../constants/auth-ui.const';

type LoginStatus = 'idle' | 'loading' | 'error' | 'success';

interface LoginState {
  status: LoginStatus;
  error?: string;
}

@Component({
  selector: 'app-login-page',
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterModule,
    TopbarComponent,
    FooterComponent,
    NzAlertModule,
    NzButtonModule,
    NzFormModule,
    NzInputModule
  ],
  templateUrl: './login-page.component.html',
  styleUrl: './login-page.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class LoginPageComponent implements OnDestroy {
  private readonly authService = inject(AuthService);
  private readonly formBuilder = inject(FormBuilder);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);

  private readonly submit$ = new Subject<void>();

  readonly navLinks: TopbarLink[] = [
    { id: 'inicio', label: 'Inicio', path: '/', fragment: 'inicio' },
    { id: 'cursos', label: 'Cursos', path: '/', fragment: 'cursos' },
    { id: 'contacto', label: 'Contacto', path: '/contacto' }
  ];

  readonly loginForm = this.formBuilder.nonNullable.group({
    correo: ['', [Validators.required, Validators.email]],
    contrasena: ['', [Validators.required]]
  });

  readonly loginState$ = this.submit$.pipe(
    tap(() => this.loginForm.markAllAsTouched()),
    filter(() => this.loginForm.valid),
    switchMap(() =>
      this.authService.login(this.loginForm.getRawValue()).pipe(
        switchMap(() => this.authService.me()),
        switchMap((user) => this.handleRoleRedirect(user.role)),
        catchError((error) => of<LoginState>(this.mapError(error))),
        startWith<LoginState>({ status: 'loading' })
      )
    ),
    startWith<LoginState>({ status: 'idle' }),
    tap((state) => {
      if (state.status === 'error') {
        this.openAlert();
      }
    }),
    shareReplay({ bufferSize: 1, refCount: true })
  );

  readonly roleAlert$ = this.route.queryParamMap.pipe(
    map((params: ParamMap) => params.get(LOGIN_QUERY_PARAMS.status)),
    map((status: string | null) => {
      if (status === LOGIN_STATUS.roleUnavailable) {
        return LOGIN_STATUS_MESSAGES.roleUnavailable;
      }
      if (status === LOGIN_STATUS.logoutError) {
        return LOGIN_STATUS_MESSAGES.logoutError;
      }
      return null;
    }),
    tap((message) => {
      if (message) {
        this.isRoleAlertVisible = true;
      }
    }),
    startWith(null),
    shareReplay({ bufferSize: 1, refCount: true })
  );

  isAlertVisible = false;
  isRoleAlertVisible = false;

  onAlertClose(): void {
    this.isAlertVisible = false;
  }

  onRoleAlertClose(): void {
    this.isRoleAlertVisible = false;
  }

  private openAlert(): void {
    this.isAlertVisible = true;
  }

  private handleRoleRedirect(role: string) {
    const target = AUTH_ROLE_ROUTES[role as keyof typeof AUTH_ROLE_ROUTES];
    if (!target) {
      return of<LoginState>({ status: 'error', error: LOGIN_STATUS_MESSAGES.roleUnavailable });
    }

    if (!this.router.config.some((route) => `/${route.path}` === target)) {
      return of<LoginState>({ status: 'error', error: LOGIN_STATUS_MESSAGES.roleUnavailable });
    }

    this.router.navigate([target]);
    return of<LoginState>({ status: 'success' });
  }

  private mapError(error: unknown): LoginState {
    const message =
      (error as { error?: { message?: string } })?.error?.message ??
      LOGIN_ERROR_MESSAGES.invalidCredentials;
    return {
      status: 'error',
      error: message
    };
  }

  onSubmit(): void {
    this.submit$.next();
  }

  isInvalid(controlName: 'correo' | 'contrasena'): boolean {
    const control = this.loginForm.get(controlName);
    return Boolean(control && control.invalid && (control.dirty || control.touched));
  }

  ngOnDestroy(): void {
    this.submit$.complete();
  }
}
