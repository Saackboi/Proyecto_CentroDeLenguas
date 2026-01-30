import { Routes } from '@angular/router';

import { authGuard } from './core/guards/auth.guard';
import { UsersPageComponent } from './features/users/pages/users-page/users-page.component';

export const routes: Routes = [
  {
    path: 'usuarios',
    component: UsersPageComponent,
    canActivate: [authGuard]
  },
  {
    path: '',
    pathMatch: 'full',
    redirectTo: 'usuarios'
  }
];
