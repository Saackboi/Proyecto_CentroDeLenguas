import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { RouterModule } from '@angular/router';
import { Subject, map, startWith, switchMap } from 'rxjs';
import { NzTooltipModule } from 'ng-zorro-antd/tooltip';

import { AdminDashboardService } from '../../data-access/services/admin-dashboard.service';
import { DashboardCounts } from '../../data-access/models/admin-dashboard.model';
import { AdminNoticesService } from '../../data-access/services/admin-notices.service';
import { AdminNoticeType } from '../../data-access/models/admin-notice.model';

interface DashboardCard {
  label: string;
  value: number;
  icon: string;
}

@Component({
  selector: 'app-admin-dashboard',
  imports: [CommonModule, RouterModule, NzTooltipModule],
  templateUrl: './admin-dashboard.component.html',
  styleUrls: ['./admin-dashboard.component.css'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AdminDashboardComponent {
  private readonly dashboardService = inject(AdminDashboardService);
  private readonly noticesService = inject(AdminNoticesService);
  private readonly refreshNotices$ = new Subject<void>();

  readonly counts$ = this.dashboardService.getDashboardCounts().pipe(
    startWith<DashboardCounts>({
      estudiantes: 0,
      profesores: 0,
      grupos: 0,
      solicitudes: 0
    })
  );

  readonly cards$ = this.counts$.pipe(
    map((counts) => [
      {
        label: 'Estudiantes',
        value: counts.estudiantes,
        icon: 'groups'
      },
      {
        label: 'Profesores',
        value: counts.profesores,
        icon: 'cast_for_education'
      },
      {
        label: 'Grupos',
        value: counts.grupos,
        icon: 'class'
      },
      {
        label: 'Solicitudes',
        value: counts.solicitudes,
        icon: 'task_alt'
      }
    ] as DashboardCard[])
  );

  readonly notices$ = this.refreshNotices$.pipe(
    startWith(void 0),
    switchMap(() => this.noticesService.getNotices())
  );


  dismissNotice(id: string): void {
    this.noticesService.dismissNotice(id);
    this.refreshNotices$.next();
  }


  noticeDotClass(type: AdminNoticeType): string {
    const typeMap: Record<AdminNoticeType, string> = {
      ubicacion: 'admin-panel__dot--danger',
      verano: 'admin-panel__dot--accent',
      abono: 'admin-panel__dot--success'
    };

    return typeMap[type];
  }
}
