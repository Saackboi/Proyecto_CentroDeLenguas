import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, ParamMap, RouterModule } from '@angular/router';
import { Store } from '@ngrx/store';
import { combineLatest, map, shareReplay, startWith, tap } from 'rxjs';

import { NzAlertModule } from 'ng-zorro-antd/alert';
import { NzButtonModule } from 'ng-zorro-antd/button';
import { NzFormModule } from 'ng-zorro-antd/form';
import { NzInputModule } from 'ng-zorro-antd/input';
import { AuthActions } from '../../../../core/store/auth/auth.actions';
import { selectAuthError, selectAuthLoading } from '../../../../core/store/auth/auth.selectors';
import {
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
    NzAlertModule,
    NzButtonModule,
    NzFormModule,
    NzInputModule
  ],
  templateUrl: './login-page.component.html',
  styleUrl: './login-page.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class LoginPageComponent {
  private readonly formBuilder = inject(FormBuilder);
  private readonly route = inject(ActivatedRoute);
  private readonly store = inject(Store);

  readonly loginForm = this.formBuilder.nonNullable.group({
    correo: ['', [Validators.required, Validators.email]],
    contrasena: ['', [Validators.required]]
  });

  readonly isLoading$ = this.store.select(selectAuthLoading);
  readonly error$ = this.store.select(selectAuthError);

  readonly loginState$ = combineLatest([this.isLoading$, this.error$]).pipe(
    map(([isLoading, error]) => {
      if (isLoading) {
        return { status: 'loading' } as LoginState;
      }
      if (error) {
        return { status: 'error', error } as LoginState;
      }
      return { status: 'idle' } as LoginState;
    }),
    startWith<LoginState>({ status: 'idle' }),
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

  onSubmit(): void {
    this.loginForm.markAllAsTouched();
    if (this.loginForm.invalid) {
      return;
    }

    this.openAlert();
    this.store.dispatch(AuthActions.login({ credentials: this.loginForm.getRawValue() }));
  }

  isInvalid(controlName: 'correo' | 'contrasena'): boolean {
    const control = this.loginForm.get(controlName);
    return Boolean(control && control.invalid && (control.dirty || control.touched));
  }
}
