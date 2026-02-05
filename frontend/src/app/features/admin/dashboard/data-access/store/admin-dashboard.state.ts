import { AdminNotice } from '../models/admin-notice.model';
import { DashboardCounts } from '../models/admin-dashboard.model';

export interface AdminDashboardState {
  counts: DashboardCounts;
  notices: AdminNotice[];
  isLoadingCounts: boolean;
  isLoadingNotices: boolean;
  errorCounts: string | null;
  errorNotices: string | null;
}

export const initialAdminDashboardState: AdminDashboardState = {
  counts: {
    estudiantes: 0,
    profesores: 0,
    grupos: 0,
    solicitudes: 0
  },
  notices: [],
  isLoadingCounts: false,
  isLoadingNotices: false,
  errorCounts: null,
  errorNotices: null
};
