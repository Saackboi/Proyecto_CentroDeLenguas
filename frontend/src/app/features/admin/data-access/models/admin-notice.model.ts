export type AdminNoticeType = 'ubicacion' | 'verano' | 'abono';

export interface AdminNotice {
  id: string;
  type: AdminNoticeType;
  title: string;
  subtitle: string;
  timeLabel: string;
  tooltip: string;
  timestamp: number;
}
