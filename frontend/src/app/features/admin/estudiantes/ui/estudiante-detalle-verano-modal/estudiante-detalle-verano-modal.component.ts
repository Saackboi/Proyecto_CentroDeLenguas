import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, EventEmitter, Input, Output } from '@angular/core';
import { NzAlertModule } from 'ng-zorro-antd/alert';
import { NzButtonModule } from 'ng-zorro-antd/button';
import { NzModalModule } from 'ng-zorro-antd/modal';
import { NzSpinModule } from 'ng-zorro-antd/spin';

import { EstudianteDetalleVeranoView } from '../../data-access/models/admin-estudiantes.model';

@Component({
  selector: 'app-estudiante-detalle-verano-modal',
  standalone: true,
  imports: [CommonModule, NzAlertModule, NzButtonModule, NzModalModule, NzSpinModule],
  templateUrl: './estudiante-detalle-verano-modal.component.html',
  styleUrl: './estudiante-detalle-verano-modal.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class EstudianteDetalleVeranoModalComponent {
  @Input() visible = false;
  @Input() detalle: EstudianteDetalleVeranoView | null = null;
  @Input() isLoading = false;
  @Input() errorMessage: string | null = null;

  @Output() close = new EventEmitter<void>();

  onClose(): void {
    this.close.emit();
  }
}
