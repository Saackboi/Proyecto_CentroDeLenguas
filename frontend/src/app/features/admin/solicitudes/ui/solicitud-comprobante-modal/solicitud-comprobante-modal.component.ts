import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, EventEmitter, Input, Output } from '@angular/core';
import { NzButtonModule } from 'ng-zorro-antd/button';
import { NzModalModule } from 'ng-zorro-antd/modal';

export interface ComprobanteImagen {
  label: string;
  url: string | null;
}

@Component({
  selector: 'app-solicitud-comprobante-modal',
  standalone: true,
  imports: [CommonModule, NzButtonModule, NzModalModule],
  templateUrl: './solicitud-comprobante-modal.component.html',
  styleUrl: './solicitud-comprobante-modal.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class SolicitudComprobanteModalComponent {
  @Input() visible = false;
  @Input() title = 'Comprobante';
  @Input() images: ComprobanteImagen[] = [];

  @Output() close = new EventEmitter<void>();

  get hasImages(): boolean {
    return this.images.some((image) => Boolean(image.url));
  }

  onClose(): void {
    this.close.emit();
  }
}
