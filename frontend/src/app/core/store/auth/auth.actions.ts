import { createActionGroup, emptyProps, props } from '@ngrx/store';

import { AuthMeDto, LoginRequestDto } from '../../models/auth.dto';

export const AuthActions = createActionGroup({
  source: 'Auth',
  events: {
    'Load Session': emptyProps(),
    'Load Session Success': props<{ user: AuthMeDto }>(),
    'Load Session Failure': props<{ error: string }>(),
    'Login': props<{ credentials: LoginRequestDto }>(),
    'Login Success': props<{ user: AuthMeDto }>(),
    'Login Failure': props<{ error: string }>(),
    'Logout': emptyProps(),
    'Logout Success': emptyProps(),
    'Logout Failure': props<{ error: string }>()
  }
});
