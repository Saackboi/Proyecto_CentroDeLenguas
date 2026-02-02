import { CommonModule, DOCUMENT } from '@angular/common';
import { AfterViewInit, ChangeDetectionStrategy, Component, OnDestroy, inject } from '@angular/core';

import { FooterComponent } from '../../../../shared/components/footer/footer.component';
import { TopbarComponent, TopbarLink } from '../../../../shared/components/topbar/topbar.component';

@Component({
  selector: 'app-inicio-page',
  imports: [CommonModule, TopbarComponent, FooterComponent],
  templateUrl: './inicio-page.component.html',
  styleUrl: './inicio-page.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class InicioPageComponent implements AfterViewInit, OnDestroy {
  private readonly document = inject(DOCUMENT);
  private observer?: IntersectionObserver;

  readonly navLinks: TopbarLink[] = [
    { id: 'inicio', label: 'Inicio', path: '/', fragment: 'inicio' },
    { id: 'cursos', label: 'Cursos', path: '/', fragment: 'cursos' },
    { id: 'contacto', label: 'Contacto', path: '/contacto' }
  ];

  activeSection = 'inicio';

  ngAfterViewInit(): void {
    const hash = this.document.location?.hash?.replace('#', '');
    if (hash) {
      this.activeSection = hash;
    }

    const sections = this.navLinks
      .map((link) => this.document.getElementById(link.id))
      .filter((section): section is HTMLElement => Boolean(section));

    if (sections.length === 0) {
      return;
    }

    this.observer = new IntersectionObserver(
      (entries) => {
        const visible = entries
          .filter((entry) => entry.isIntersecting)
          .sort((a, b) => b.intersectionRatio - a.intersectionRatio);

        if (visible[0]) {
          this.activeSection = (visible[0].target as HTMLElement).id;
        }
      },
      {
        root: null,
        threshold: [0.25, 0.5, 0.75]
      }
    );

    sections.forEach((section) => this.observer?.observe(section));
  }

  ngOnDestroy(): void {
    this.observer?.disconnect();
  }
}
