import { CommonModule, DOCUMENT } from '@angular/common';
import { AfterViewInit, Component, OnDestroy, inject } from '@angular/core';
import { Router, RouterModule, RouterOutlet, NavigationEnd } from '@angular/router';
import { Subject, filter, takeUntil } from 'rxjs';

import { FooterComponent } from '../../shared/components/footer/footer.component';
import { TopbarComponent, TopbarLink } from '../../shared/components/topbar/topbar.component';

@Component({
  selector: 'app-public-layout',
  imports: [CommonModule, RouterModule, RouterOutlet, TopbarComponent, FooterComponent],
  templateUrl: './public-layout.html',
  styleUrl: './public-layout.css'
})
export class PublicLayout implements AfterViewInit, OnDestroy {
  private readonly document = inject(DOCUMENT);
  private readonly router = inject(Router);
  private readonly destroy$ = new Subject<void>();

  readonly navLinks: TopbarLink[] = [
    { id: 'inicio', label: 'Inicio', path: '/', fragment: 'inicio' },
    { id: 'cursos', label: 'Cursos', path: '/', fragment: 'cursos' },
    { id: 'contacto', label: 'Contacto', path: '/contacto' }
  ];

  activeSection = 'inicio';

  ngAfterViewInit(): void {
    this.syncActiveSection();
    this.router.events
      .pipe(filter((event) => event instanceof NavigationEnd), takeUntil(this.destroy$))
      .subscribe(() => {
        this.syncActiveSection();
      });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  private syncActiveSection(): void {
    const path = this.router.url.split('#')[0];
    const hash = this.document.location?.hash?.replace('#', '');

    if (path === '/contacto') {
      this.activeSection = 'contacto';
      return;
    }

    if (path === '/login') {
      this.activeSection = '';
      return;
    }

    this.activeSection = hash || 'inicio';
  }
}
