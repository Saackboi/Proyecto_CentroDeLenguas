import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, OnInit, inject } from '@angular/core';
import { RouterModule } from '@angular/router';
import { Store } from '@ngrx/store';
import { map } from 'rxjs';
import { NzTooltipModule } from 'ng-zorro-antd/tooltip';

import { LoadingOverlayComponent } from '../../../../shared/components/loading-overlay/loading-overlay';
import { AdminNoticeType } from '../../dashboard/data-access/models/admin-notice.model';
import { AdminDashboardActions } from '../../dashboard/data-access/store/admin-dashboard.actions';
import {
  selectAdminDashboardCounts,
  selectAdminDashboardLoading,
  selectAdminDashboardNotices
} from '../../dashboard/data-access/store/admin-dashboard.selectors';

interface DashboardCard {
  label: string;
  value: number;
  icon: string;
}

@Component({
  selector: 'app-admin-dashboard',
  imports: [CommonModule, RouterModule, NzTooltipModule, LoadingOverlayComponent],
  templateUrl: './admin-dashboard.component.html',
  styleUrls: ['./admin-dashboard.component.css'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AdminDashboardComponent implements OnInit {
  private readonly store = inject(Store);

  readonly counts$ = this.store.select(selectAdminDashboardCounts);
  readonly isLoading$ = this.store.select(selectAdminDashboardLoading);

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

  readonly notices$ = this.store.select(selectAdminDashboardNotices);

  ngOnInit(): void {
    this.store.dispatch(AdminDashboardActions.loadCounts());
    this.store.dispatch(AdminDashboardActions.loadNotices());
  }

  dismissNotice(id: string): void {
    this.store.dispatch(AdminDashboardActions.dismissNotice({ id }));
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
