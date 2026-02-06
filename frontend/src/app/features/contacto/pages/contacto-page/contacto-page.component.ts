import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component } from '@angular/core';

@Component({
  selector: 'app-contacto-page',
  imports: [CommonModule],
  templateUrl: './contacto-page.component.html',
  styleUrl: './contacto-page.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ContactoPageComponent {}
