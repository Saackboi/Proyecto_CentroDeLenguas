import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, OnInit, inject } from '@angular/core';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { Store } from '@ngrx/store';
import { map } from 'rxjs';
import { NzButtonModule } from 'ng-zorro-antd/button';
import { NzSelectModule } from 'ng-zorro-antd/select';
import { NzSpinModule } from 'ng-zorro-antd/spin';
import { NzTooltipModule } from 'ng-zorro-antd/tooltip';

import { LoadingOverlayComponent } from '../../../../shared/components/loading-overlay/loading-overlay';
import { LandingAnnouncementStatus } from '../../../../shared/models/landing-announcement.model';
import { AdminNoticeType } from '../../dashboard/data-access/models/admin-notice.model';
import { AdminDashboardActions } from '../../dashboard/data-access/store/admin-dashboard.actions';
import {
  selectAdminDashboardCounts,
  selectAdminDashboardLoading,
  selectAdminDashboardNotices,
  selectAdminDashboardLoadingNotices
} from '../../dashboard/data-access/store/admin-dashboard.selectors';
import {
  ADMIN_LANDING_STATUS_OPTIONS
} from '../../landing/data-access/constants/admin-landing.const';
import { AdminLandingActions } from '../../landing/data-access/store/admin-landing.actions';
import {
  selectAdminLandingAnnouncement,
  selectAdminLandingLoading,
  selectAdminLandingUpdating
} from '../../landing/data-access/store/admin-landing.selectors';

interface DashboardCard {
  label: string;
  value: number;
  icon: string;
}

@Component({
  selector: 'app-admin-dashboard',
  imports: [
    CommonModule,
    FormsModule,
    RouterModule,
    NzButtonModule,
    NzSelectModule,
    NzSpinModule,
    NzTooltipModule,
    LoadingOverlayComponent
  ],
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
  readonly noticesLoading$ = this.store.select(selectAdminDashboardLoadingNotices);
  readonly landingAnnouncement$ = this.store.select(selectAdminLandingAnnouncement);
  readonly landingLoading$ = this.store.select(selectAdminLandingLoading);
  readonly landingUpdating$ = this.store.select(selectAdminLandingUpdating);
  readonly landingStatusOptions = ADMIN_LANDING_STATUS_OPTIONS;

  selectedLandingStatus: LandingAnnouncementStatus | null = null;

  ngOnInit(): void {
    this.store.dispatch(AdminDashboardActions.loadCounts());
    this.store.dispatch(AdminDashboardActions.loadNotices());
    this.store.dispatch(AdminLandingActions.loadAnnouncement());
  }

  dismissNotice(id: string): void {
    this.store.dispatch(AdminDashboardActions.dismissNotice({ id }));
  }

  onLandingStatusChange(status: LandingAnnouncementStatus): void {
    this.selectedLandingStatus = status;
  }

  onSaveLandingStatus(status: LandingAnnouncementStatus | null): void {
    if (!status) {
      return;
    }
    this.store.dispatch(AdminLandingActions.updateAnnouncement({
      statusCode: status
    }));
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
