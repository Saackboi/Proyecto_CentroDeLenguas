import { createActionGroup, emptyProps, props } from '@ngrx/store';

import { LandingAnnouncementView } from '../../../../../shared/models/landing-announcement.model';

export const AdminLandingActions = createActionGroup({
  source: 'Admin Landing',
  events: {
    'Load Announcement': emptyProps(),
    'Load Announcement Success': props<{ announcement: LandingAnnouncementView }>(),
    'Load Announcement Failure': props<{ error: string }>(),
    'Update Announcement': props<{ statusCode: LandingAnnouncementView['statusCode'] }>(),
    'Update Announcement Success': props<{ announcement: LandingAnnouncementView }>(),
    'Update Announcement Failure': props<{ error: string }>()
  }
});
