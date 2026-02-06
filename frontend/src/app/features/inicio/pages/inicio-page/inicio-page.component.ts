import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component } from '@angular/core';

@Component({
  selector: 'app-inicio-page',
  imports: [CommonModule],
  templateUrl: './inicio-page.component.html',
  styleUrl: './inicio-page.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class InicioPageComponent {
}
