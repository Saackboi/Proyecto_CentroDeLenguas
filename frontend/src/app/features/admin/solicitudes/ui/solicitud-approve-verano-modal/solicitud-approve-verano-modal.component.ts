import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, EventEmitter, Input, OnChanges, Output, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { NzButtonModule } from 'ng-zorro-antd/button';
import { NzDatePickerModule } from 'ng-zorro-antd/date-picker';
import { NzFormModule } from 'ng-zorro-antd/form';
import { NzInputModule } from 'ng-zorro-antd/input';
import { NzModalModule } from 'ng-zorro-antd/modal';
import { NzSelectModule } from 'ng-zorro-antd/select';

import {
  SolicitudSexo,
  SolicitudVeranoApprovalPayload,
  SolicitudVeranoView
} from '../../../data-access/models/admin-solicitudes.model';

type EstadoVerano = 'Activo' | 'Inactivo' | 'En proceso';

@Component({
  selector: 'app-solicitud-approve-verano-modal',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    NzButtonModule,
    NzDatePickerModule,
    NzFormModule,
    NzInputModule,
    NzModalModule,
    NzSelectModule
  ],
  templateUrl: './solicitud-approve-verano-modal.component.html',
  styleUrl: './solicitud-approve-verano-modal.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class SolicitudApproveVeranoModalComponent implements OnChanges {
  private readonly formBuilder = inject(FormBuilder);

  @Input() visible = false;
  @Input() solicitud: SolicitudVeranoView | null = null;
  @Input() isSubmitting = false;

  @Output() cancel = new EventEmitter<void>();
  @Output() confirm = new EventEmitter<SolicitudVeranoApprovalPayload>();

  readonly form = this.formBuilder.group({
    idEstudiante: [{ value: '', disabled: true }, [Validators.required]],
    estado: ['En proceso' as EstadoVerano, [Validators.required]],
    nivel: ['', [Validators.required]],
    nombreCompleto: ['', [Validators.required]],
    celular: ['', [Validators.required]],
    fechaNacimiento: [null as Date | null, [Validators.required]],
    numeroCasa: [''],
    domicilio: ['', [Validators.required]],
    sexo: [null as SolicitudSexo | null, [Validators.required]],
    correo: ['', [Validators.required, Validators.email]],
    colegio: ['', [Validators.required]],
    tipoSangre: ['', [Validators.required]],
    nombreMadre: [''],
    lugarTrabajoMadre: [''],
    telefonoTrabajoMadre: [''],
    celularMadre: [''],
    nombrePadre: [''],
    lugarTrabajoPadre: [''],
    telefonoTrabajoPadre: [''],
    celularPadre: [''],
    alergias: [''],
    contactoNombre: [''],
    contactoTelefono: ['']
  });

  ngOnChanges(): void {
    if (!this.solicitud) {
      this.form.reset({
        idEstudiante: { value: '', disabled: true },
        estado: 'En proceso',
        nivel: '',
        nombreCompleto: '',
        celular: '',
        fechaNacimiento: null,
        numeroCasa: '',
        domicilio: '',
        sexo: null,
        correo: '',
        colegio: '',
        tipoSangre: '',
        nombreMadre: '',
        lugarTrabajoMadre: '',
        telefonoTrabajoMadre: '',
        celularMadre: '',
        nombrePadre: '',
        lugarTrabajoPadre: '',
        telefonoTrabajoPadre: '',
        celularPadre: '',
        alergias: '',
        contactoNombre: '',
        contactoTelefono: ''
      });
      return;
    }

    this.form.reset({
      idEstudiante: { value: this.solicitud.id, disabled: true },
      estado: (this.solicitud.estado === 'Sin estado' ? 'En proceso' : this.solicitud.estado) as EstadoVerano,
      nivel: '',
      nombreCompleto: this.solicitud.nombreCompleto,
      celular: this.solicitud.telefono,
      fechaNacimiento: this.parseDate(this.solicitud.fechaNacimiento),
      numeroCasa: this.solicitud.numeroCasa ?? '',
      domicilio: this.solicitud.domicilio ?? '',
      sexo: this.solicitud.sexo,
      correo: this.solicitud.correo,
      colegio: this.solicitud.colegio ?? '',
      tipoSangre: this.solicitud.tipoSangre ?? '',
      nombreMadre: this.solicitud.nombreMadre ?? '',
      lugarTrabajoMadre: this.solicitud.lugarTrabajoMadre ?? '',
      telefonoTrabajoMadre: this.solicitud.telefonoTrabajoMadre ?? '',
      celularMadre: this.solicitud.celularMadre ?? '',
      nombrePadre: this.solicitud.nombrePadre ?? '',
      lugarTrabajoPadre: this.solicitud.lugarTrabajoPadre ?? '',
      telefonoTrabajoPadre: this.solicitud.telefonoTrabajoPadre ?? '',
      celularPadre: this.solicitud.celularPadre ?? '',
      alergias: this.solicitud.alergias ?? '',
      contactoNombre: this.solicitud.contactoNombre ?? '',
      contactoTelefono: this.solicitud.contactoTelefono ?? ''
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
    const fechaNacimiento = this.formatDate(raw.fechaNacimiento);
    if (!fechaNacimiento) {
      this.form.markAllAsTouched();
      return;
    }

    this.confirm.emit({
      id_estudiante: raw.idEstudiante ?? this.solicitud.id,
      estado: (raw.estado ?? 'En proceso') as EstadoVerano,
      nivel: (raw.nivel ?? '').trim(),
      nombre_completo: (raw.nombreCompleto ?? '').trim(),
      celular: (raw.celular ?? '').trim(),
      fecha_nacimiento: fechaNacimiento,
      numero_casa: raw.numeroCasa?.trim() || null,
      domicilio: (raw.domicilio ?? '').trim(),
      sexo: raw.sexo as SolicitudSexo,
      correo: (raw.correo ?? '').trim(),
      colegio: (raw.colegio ?? '').trim(),
      tipo_sangre: (raw.tipoSangre ?? '').trim(),
      nombre_madre: raw.nombreMadre?.trim() || null,
      lugar_trabajo_madre: raw.lugarTrabajoMadre?.trim() || null,
      telefono_trabajo_madre: raw.telefonoTrabajoMadre?.trim() || null,
      celular_madre: raw.celularMadre?.trim() || null,
      nombre_padre: raw.nombrePadre?.trim() || null,
      lugar_trabajo_padre: raw.lugarTrabajoPadre?.trim() || null,
      telefono_trabajo_padre: raw.telefonoTrabajoPadre?.trim() || null,
      celular_padre: raw.celularPadre?.trim() || null,
      alergias: raw.alergias?.trim() || null,
      contacto_nombre: raw.contactoNombre?.trim() || null,
      contacto_telefono: raw.contactoTelefono?.trim() || null
    });
  }

  private parseDate(value: string | null): Date | null {
    if (!value) {
      return null;
    }
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? null : date;
  }

  private formatDate(value: Date | null): string | null {
    if (!value) {
      return null;
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
      return null;
    }
    return date.toISOString().split('T')[0];
  }
}
