import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, of, switchMap, tap, throwError } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiResponseDto } from '../models/api-response.dto';
import { AuthTokenDto, LoginRequestDto } from '../models/auth.dto';
import { AUTH_API_PATHS, AUTH_STORAGE_KEYS } from '../constants/auth.const';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly loginUrl = `${environment.apiBaseUrl}${AUTH_API_PATHS.login}`;

  constructor(private readonly http: HttpClient) {}

  login(request: LoginRequestDto): Observable<AuthTokenDto> {
    return this.http.post<ApiResponseDto<AuthTokenDto>>(this.loginUrl, request).pipe(
      switchMap((response) =>
        response.data ? of(response.data) : throwError(() => new Error('Token faltante'))
      ),
      tap((token) => this.setToken(token.access_token))
    );
  }

  getToken(): string | null {
    return localStorage.getItem(AUTH_STORAGE_KEYS.accessToken);
  }

  setToken(token: string): void {
    localStorage.setItem(AUTH_STORAGE_KEYS.accessToken, token);
  }

  clearToken(): void {
    localStorage.removeItem(AUTH_STORAGE_KEYS.accessToken);
  }

  hasToken(): boolean {
    return Boolean(this.getToken());
  }
}
