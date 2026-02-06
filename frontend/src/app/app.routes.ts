import { Routes } from '@angular/router';

import { authGuard } from './core/guards/auth.guard';
import { guestGuard } from './core/guards/guest.guard';
import { LoginPageComponent } from './features/auth/pages/login-page/login-page.component';
import { AdminDashboardComponent } from './features/admin/pages/admin-dashboard/admin-dashboard.component';
import { AdminSolicitudesComponent } from './features/admin/pages/admin-solicitudes/admin-solicitudes.component';
import { AdminLayoutComponent } from './features/admin/layout/admin-layout/admin-layout.component';
import { ContactoPageComponent } from './features/contacto/pages/contacto-page/contacto-page.component';
import { InicioPageComponent } from './features/inicio/pages/inicio-page/inicio-page.component';
import { PublicLayout } from './layout/public-layout/public-layout';

export const routes: Routes = [
  {
    path: '',
    component: PublicLayout,
    children: [
      {
        path: '',
        component: InicioPageComponent
      },
      {
        path: 'contacto',
        component: ContactoPageComponent
      },
      {
        path: 'login',
        component: LoginPageComponent,
        canActivate: [guestGuard]
      }
    ]
  },
  {
    path: 'admin',
    component: AdminLayoutComponent,
    canActivate: [authGuard],
    children: [
      {
        path: '',
        component: AdminDashboardComponent
      },
      {
        path: 'solicitudes',
        component: AdminSolicitudesComponent
      }
    ]
  },
  {
    path: 'usuarios',
    redirectTo: '',
    pathMatch: 'full'
  }
];
