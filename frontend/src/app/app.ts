import { Component, signal, inject } from '@angular/core';
import { Router, RouterOutlet, NavigationStart } from '@angular/router';
import { DOCUMENT } from '@angular/common';
import { filter } from 'rxjs';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App {
  protected readonly title = signal('frontend');

  private readonly router = inject(Router);
  private readonly document = inject(DOCUMENT);

  constructor() {
    const doc = this.document as Document & {
      startViewTransition?: (callback: () => void) => void;
    };

    if (!doc.startViewTransition) {
      return;
    }

    this.router.events
      .pipe(filter((event) => event instanceof NavigationStart))
      .subscribe(() => {
        doc.startViewTransition?.(() => undefined);
      });
  }
}
