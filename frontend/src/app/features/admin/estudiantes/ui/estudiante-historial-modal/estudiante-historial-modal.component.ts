import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, EventEmitter, Input, Output } from '@angular/core';
import { NzAlertModule } from 'ng-zorro-antd/alert';
import { NzButtonModule } from 'ng-zorro-antd/button';
import { NzModalModule } from 'ng-zorro-antd/modal';
import { NzSpinModule } from 'ng-zorro-antd/spin';
import { NzTableModule } from 'ng-zorro-antd/table';

import { EstudianteHistorialView } from '../../data-access/models/admin-estudiantes.model';

@Component({
  selector: 'app-estudiante-historial-modal',
  standalone: true,
  imports: [CommonModule, NzAlertModule, NzButtonModule, NzModalModule, NzSpinModule, NzTableModule],
  templateUrl: './estudiante-historial-modal.component.html',
  styleUrl: './estudiante-historial-modal.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class EstudianteHistorialModalComponent {
  @Input() visible = false;
  @Input() historial: EstudianteHistorialView | null = null;
  @Input() isLoading = false;
  @Input() errorMessage: string | null = null;

  @Output() close = new EventEmitter<void>();

  onClose(): void {
    this.close.emit();
  }
}
