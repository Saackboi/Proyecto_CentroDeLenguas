import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, EventEmitter, Input, OnChanges, Output, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { NzAlertModule } from 'ng-zorro-antd/alert';
import { NzButtonModule } from 'ng-zorro-antd/button';
import { NzFormModule } from 'ng-zorro-antd/form';
import { NzInputModule } from 'ng-zorro-antd/input';
import { NzModalModule } from 'ng-zorro-antd/modal';
import { NzSelectModule } from 'ng-zorro-antd/select';
import { NzSpinModule } from 'ng-zorro-antd/spin';

import {
  EstudianteDetalleRegularView,
  EstudianteEstadoRegular,
  EstudianteRegularUpdatePayload
} from '../../data-access/models/admin-estudiantes.model';

@Component({
  selector: 'app-estudiante-edit-regular-modal',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    NzAlertModule,
    NzButtonModule,
    NzFormModule,
    NzInputModule,
    NzModalModule,
    NzSelectModule,
    NzSpinModule
  ],
  templateUrl: './estudiante-edit-regular-modal.component.html',
  styleUrl: './estudiante-edit-regular-modal.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class EstudianteEditRegularModalComponent implements OnChanges {
  private readonly formBuilder = inject(FormBuilder);

  @Input() visible = false;
  @Input() detalle: EstudianteDetalleRegularView | null = null;
  @Input() isLoading = false;
  @Input() isSubmitting = false;
  @Input() errorMessage: string | null = null;

  @Output() cancel = new EventEmitter<void>();
  @Output() confirm = new EventEmitter<EstudianteRegularUpdatePayload>();

  readonly form = this.formBuilder.nonNullable.group({
    id: [{ value: '', disabled: true }, [Validators.required]],
    tipoId: ['', [Validators.required]],
    nombre: ['', [Validators.required]],
    apellido: ['', [Validators.required]],
    correoPersonal: ['', [Validators.required, Validators.email]],
    correoUtp: ['', [Validators.email]],
    telefono: ['', [Validators.required]],
    nivel: [''],
    estado: ['Activo' as EstudianteEstadoRegular, [Validators.required]],
    esEstudiante: ['NO' as 'SI' | 'NO', [Validators.required]],
    saldoPendiente: 0
  });

  ngOnChanges(): void {
    if (!this.detalle) {
      this.form.reset({
        id: { value: '', disabled: true },
        tipoId: '',
        nombre: '',
        apellido: '',
        correoPersonal: '',
        correoUtp: '',
        telefono: '',
        nivel: '',
        estado: 'Activo',
        esEstudiante: 'NO',
      saldoPendiente: 0
      });
      return;
    }

    this.form.reset({
      id: { value: this.detalle.id, disabled: true },
      tipoId: this.detalle.tipoId,
      nombre: this.detalle.nombre,
      apellido: this.detalle.apellido,
      correoPersonal: this.detalle.correoPersonal,
      correoUtp: this.detalle.correoUtp,
      telefono: this.detalle.telefono,
      nivel: this.detalle.nivel,
      estado: this.detalle.estado,
      esEstudiante: this.detalle.esEstudiante,
      saldoPendiente: this.detalle.saldoPendiente
    });
  }

  onCancel(): void {
    this.cancel.emit();
  }

  onSubmit(): void {
    if (!this.detalle) {
      return;
    }

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    const raw = this.form.getRawValue();
    const correoUtp = raw.correoUtp.trim() || null;
    const nivel = raw.nivel.trim() || null;
    const saldoPendiente = Number.isNaN(raw.saldoPendiente)
      ? null
      : Number(raw.saldoPendiente);

    this.confirm.emit({
      id_type: raw.tipoId.trim(),
      nombre: raw.nombre.trim(),
      apellido: raw.apellido.trim(),
      correo_personal: raw.correoPersonal.trim(),
      correo_utp: correoUtp,
      telefono: raw.telefono.trim(),
      nivel,
      estado: raw.estado ?? 'Activo',
      es_estudiante: raw.esEstudiante ?? 'NO',
      saldo_pendiente: saldoPendiente
    });
  }
}
