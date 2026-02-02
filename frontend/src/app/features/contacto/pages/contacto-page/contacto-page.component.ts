import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component } from '@angular/core';

import { FooterComponent } from '../../../../shared/components/footer/footer.component';
import { TopbarComponent, TopbarLink } from '../../../../shared/components/topbar/topbar.component';

@Component({
  selector: 'app-contacto-page',
  imports: [CommonModule, TopbarComponent, FooterComponent],
  templateUrl: './contacto-page.component.html',
  styleUrl: './contacto-page.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ContactoPageComponent {
  readonly navLinks: TopbarLink[] = [
    { id: 'inicio', label: 'Inicio', path: '/', fragment: 'inicio' },
    { id: 'cursos', label: 'Cursos', path: '/', fragment: 'cursos' },
    { id: 'contacto', label: 'Contacto', path: '/contacto' }
  ];

  readonly activeSection = 'contacto';
}
