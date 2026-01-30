import { Injectable, inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { catchError, map, of, switchMap } from 'rxjs';

import { USERS_ERROR_MESSAGES } from '../constants/users.const';
import { UsersService } from '../services/users.service';
import { UsersActions } from './users.actions';

@Injectable()
export class UsersEffects {
  private readonly actions$ = inject(Actions);
  private readonly usersService = inject(UsersService);

  loadUsers$ = createEffect(() =>
    this.actions$.pipe(
      ofType(UsersActions.loadUsers),
      switchMap(() =>
        this.usersService.getUsers().pipe(
          map((users) => UsersActions.loadUsersSuccess({ users })),
          catchError(() =>
            of(UsersActions.loadUsersFailure({ error: USERS_ERROR_MESSAGES.loadFailed }))
          )
        )
      )
    )
  );

  constructor() {}
}
