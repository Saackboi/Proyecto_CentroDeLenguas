import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, EventEmitter, Input, Output, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { NzButtonModule } from 'ng-zorro-antd/button';
import { NzFormModule } from 'ng-zorro-antd/form';
import { NzInputModule } from 'ng-zorro-antd/input';
import { NzModalModule } from 'ng-zorro-antd/modal';
import { NzAlertModule } from 'ng-zorro-antd/alert';

@Component({
  selector: 'app-solicitud-reject-modal',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    NzButtonModule,
    NzAlertModule,
    NzFormModule,
    NzInputModule,
    NzModalModule
  ],
  templateUrl: './solicitud-reject-modal.component.html',
  styleUrl: './solicitud-reject-modal.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class SolicitudRejectModalComponent {
  private readonly formBuilder = inject(FormBuilder);

  @Input() visible = false;
  @Input() title = 'Rechazar solicitud';
  @Input() description = 'Indica el motivo del rechazo.';
  @Input() isSubmitting = false;
  @Input() errorMessage: string | null = null;

  @Output() cancel = new EventEmitter<void>();
  @Output() confirm = new EventEmitter<string>();

  readonly form = this.formBuilder.nonNullable.group({
    motivo: ['', [Validators.required]]
  });

  onCancel(): void {
    this.form.reset();
    this.cancel.emit();
  }

  onSubmit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    const motivo = this.form.controls.motivo.value.trim();
    if (!motivo) {
      this.form.controls.motivo.setErrors({ required: true });
      return;
    }

    this.confirm.emit(motivo);
    this.form.reset();
  }
}
