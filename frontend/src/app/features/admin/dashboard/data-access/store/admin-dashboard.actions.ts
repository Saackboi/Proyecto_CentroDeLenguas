import { createActionGroup, emptyProps, props } from '@ngrx/store';

import { AdminNotice } from '../models/admin-notice.model';
import { DashboardCounts } from '../models/admin-dashboard.model';

export const AdminDashboardActions = createActionGroup({
  source: 'Admin Dashboard',
  events: {
    'Load Counts': emptyProps(),
    'Load Counts Success': props<{ counts: DashboardCounts }>(),
    'Load Counts Failure': props<{ error: string }>(),
    'Load Notices': emptyProps(),
    'Load Notices Success': props<{ notices: AdminNotice[] }>(),
    'Load Notices Failure': props<{ error: string }>(),
    'Dismiss Notice': props<{ id: string }>()
  }
});
