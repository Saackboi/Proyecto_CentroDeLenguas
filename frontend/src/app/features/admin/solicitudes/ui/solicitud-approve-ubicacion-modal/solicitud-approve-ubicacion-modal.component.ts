import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, EventEmitter, Input, OnChanges, Output, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { NzButtonModule } from 'ng-zorro-antd/button';
import { NzFormModule } from 'ng-zorro-antd/form';
import { NzInputModule } from 'ng-zorro-antd/input';
import { NzModalModule } from 'ng-zorro-antd/modal';
import { NzAlertModule } from 'ng-zorro-antd/alert';
import { NzSelectModule } from 'ng-zorro-antd/select';

import {
  SolicitudEsEstudiante,
  SolicitudUbicacionApprovalPayload,
  SolicitudUbicacionView
} from '../../../data-access/models/admin-solicitudes.model';

type EstadoAprobacion = 'Activo' | 'Inactivo';

@Component({
  selector: 'app-solicitud-approve-ubicacion-modal',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    NzButtonModule,
    NzAlertModule,
    NzFormModule,
    NzInputModule,
    NzModalModule,
    NzSelectModule
  ],
  templateUrl: './solicitud-approve-ubicacion-modal.component.html',
  styleUrl: './solicitud-approve-ubicacion-modal.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class SolicitudApproveUbicacionModalComponent implements OnChanges {
  private readonly formBuilder = inject(FormBuilder);

  @Input() visible = false;
  @Input() solicitud: SolicitudUbicacionView | null = null;
  @Input() isSubmitting = false;
  @Input() approveError: string | null = null;
  @Input() rejectError: string | null = null;

  @Output() cancel = new EventEmitter<void>();
  @Output() confirm = new EventEmitter<SolicitudUbicacionApprovalPayload>();

  readonly form = this.formBuilder.group({
    idEstudiante: [{ value: '', disabled: true }, [Validators.required]],
    tipoId: ['', [Validators.required]],
    nombre: ['', [Validators.required]],
    apellido: ['', [Validators.required]],
    correoPersonal: ['', [Validators.required, Validators.email]],
    correoUtp: ['', [Validators.email]],
    telefono: ['', [Validators.required]],
    nivel: [''],
    estado: ['Activo' as EstadoAprobacion, [Validators.required]],
    saldoPendiente: [null as number | null],
    esEstudiante: ['NO' as SolicitudEsEstudiante, [Validators.required]]
  });

  ngOnChanges(): void {
    if (!this.solicitud) {
      this.form.reset({
        idEstudiante: { value: '', disabled: true },
        tipoId: '',
        nombre: '',
        apellido: '',
        correoPersonal: '',
        correoUtp: '',
        telefono: '',
        nivel: '',
        estado: 'Activo',
        saldoPendiente: null,
        esEstudiante: 'NO'
      });
      return;
    }

    this.form.reset({
      idEstudiante: { value: this.solicitud.id, disabled: true },
      tipoId: this.solicitud.tipoId ?? '',
      nombre: this.solicitud.nombre,
      apellido: this.solicitud.apellido,
      correoPersonal: this.solicitud.correoPersonal,
      correoUtp: this.solicitud.correoUtp,
      telefono: this.solicitud.telefono,
      nivel: '',
      estado: 'Activo',
      saldoPendiente: null,
      esEstudiante: this.solicitud.correoUtp ? 'SI' : 'NO'
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
    const correoUtp = raw.correoUtp?.trim() || null;
    const nivel = raw.nivel?.trim() || null;
    const saldoPendiente = raw.saldoPendiente === null || Number.isNaN(raw.saldoPendiente)
      ? null
      : Number(raw.saldoPendiente);
    const estado = (raw.estado ?? 'Activo') as EstadoAprobacion;
    const esEstudiante = (raw.esEstudiante ?? 'NO') as SolicitudEsEstudiante;
    const idEstudiante = raw.idEstudiante ?? this.solicitud.id;
    const tipoId = (raw.tipoId ?? '').trim();
    const nombre = (raw.nombre ?? '').trim();
    const apellido = (raw.apellido ?? '').trim();
    const correoPersonal = (raw.correoPersonal ?? '').trim();
    const telefono = (raw.telefono ?? '').trim();

    this.confirm.emit({
      id_estudiante: idEstudiante,
      nivel,
      id_type: tipoId,
      nombre,
      apellido,
      correo_personal: correoPersonal,
      correo_utp: correoUtp,
      telefono,
      estado,
      saldo_pendiente: saldoPendiente,
      es_estudiante: esEstudiante
    });
  }
}
