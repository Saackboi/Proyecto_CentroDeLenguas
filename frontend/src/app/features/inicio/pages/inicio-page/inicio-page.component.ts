import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { startWith } from 'rxjs';

import { LandingAnnouncementService } from '../../data-access/services/landing-announcement.service';

@Component({
  selector: 'app-inicio-page',
  imports: [CommonModule],
  templateUrl: './inicio-page.component.html',
  styleUrl: './inicio-page.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class InicioPageComponent {
  private readonly landingAnnouncementService = inject(LandingAnnouncementService);

  readonly announcement$ = this.landingAnnouncementService.getAnnouncement().pipe(startWith(null));
}
