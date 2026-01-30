import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, map, of } from 'rxjs';

import { environment } from '../../../../../environments/environment';
import { ApiResponseDto } from '../../../../core/models/api-response.dto';
import { USER_MOCKS, USERS_API_PATHS } from '../constants/users.const';
import { mapUserDtoToUser } from '../models/user.adapter';
import { UserDto } from '../models/user.dto';
import { User } from '../models/user.model';

@Injectable({ providedIn: 'root' })
export class UsersService {
  private readonly usersMeUrl = `${environment.apiBaseUrl}${USERS_API_PATHS.me}`;

  constructor(private readonly http: HttpClient) {}

  getUsers(): Observable<User[]> {
    if (environment.useMocks) {
      return of(USER_MOCKS).pipe(map((dtos) => dtos.map(mapUserDtoToUser)));
    }

    return this.http
      .get<ApiResponseDto<UserDto>>(this.usersMeUrl)
      .pipe(
        map((response) => (response.data ? [response.data] : [])),
        map((dtos) => dtos.map(mapUserDtoToUser))
      );
  }
}
