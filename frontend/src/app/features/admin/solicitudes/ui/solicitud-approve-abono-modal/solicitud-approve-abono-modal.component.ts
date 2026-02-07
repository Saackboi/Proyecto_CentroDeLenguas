import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, EventEmitter, Input, OnChanges, Output, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { NzButtonModule } from 'ng-zorro-antd/button';
import { NzFormModule } from 'ng-zorro-antd/form';
import { NzInputModule } from 'ng-zorro-antd/input';
import { NzModalModule } from 'ng-zorro-antd/modal';
import { NzAlertModule } from 'ng-zorro-antd/alert';

import {
  SolicitudAbonoApprovalPayload,
  SolicitudAbonoView
} from '../../../data-access/models/admin-solicitudes.model';

@Component({
  selector: 'app-solicitud-approve-abono-modal',
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
  templateUrl: './solicitud-approve-abono-modal.component.html',
  styleUrl: './solicitud-approve-abono-modal.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class SolicitudApproveAbonoModalComponent implements OnChanges {
  private readonly formBuilder = inject(FormBuilder);

  @Input() visible = false;
  @Input() solicitud: SolicitudAbonoView | null = null;
  @Input() saldoPendiente: number | null = null;
  @Input() isSubmitting = false;
  @Input() isLoadingSaldo = false;
  @Input() saldoError: string | null = null;
  @Input() approveError: string | null = null;

  @Output() cancel = new EventEmitter<void>();
  @Output() confirm = new EventEmitter<SolicitudAbonoApprovalPayload>();

  readonly form = this.formBuilder.group({
    idEstudiante: [{ value: '', disabled: true }, [Validators.required]],
    abono: [null as number | null, [Validators.required]]
  });

  ngOnChanges(): void {
    if (!this.solicitud) {
      this.form.reset({
        idEstudiante: { value: '', disabled: true },
        abono: null
      });
      return;
    }

    this.form.reset({
      idEstudiante: { value: this.solicitud.idEstudiante, disabled: true },
      abono: this.solicitud.montoRaw ?? null
    });
  }

  onCancel(): void {
    this.cancel.emit();
  }

  onSubmit(): void {
    if (!this.solicitud) {
      return;
    }

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    const raw = this.form.getRawValue();
    if (raw.abono === null) {
      this.form.markAllAsTouched();
      return;
    }

    const abono = Number(raw.abono);
    if (Number.isNaN(abono)) {
      this.form.markAllAsTouched();
      return;
    }

    this.confirm.emit({
      id_estudiante: raw.idEstudiante ?? this.solicitud.idEstudiante,
      saldo_pendiente: this.saldoPendiente ?? 0,
      abono
    });
  }
}
