import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, EventEmitter, Input, Output } from '@angular/core';
import { NzButtonModule } from 'ng-zorro-antd/button';
import { NzModalModule } from 'ng-zorro-antd/modal';
import { NzSpinModule } from 'ng-zorro-antd/spin';
import { NzAlertModule } from 'ng-zorro-antd/alert';

import { EstudianteDetalleRegularView } from '../../data-access/models/admin-estudiantes.model';

@Component({
  selector: 'app-estudiante-detalle-regular-modal',
  standalone: true,
  imports: [CommonModule, NzButtonModule, NzModalModule, NzSpinModule, NzAlertModule],
  templateUrl: './estudiante-detalle-regular-modal.component.html',
  styleUrl: './estudiante-detalle-regular-modal.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class EstudianteDetalleRegularModalComponent {
  @Input() visible = false;
  @Input() detalle: EstudianteDetalleRegularView | null = null;
  @Input() isLoading = false;
  @Input() errorMessage: string | null = null;

  @Output() close = new EventEmitter<void>();

  onClose(): void {
    this.close.emit();
  }
}
