import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, EventEmitter, Input, OnChanges, Output, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { NzAlertModule } from 'ng-zorro-antd/alert';
import { NzButtonModule } from 'ng-zorro-antd/button';
import { NzDatePickerModule } from 'ng-zorro-antd/date-picker';
import { NzFormModule } from 'ng-zorro-antd/form';
import { NzInputModule } from 'ng-zorro-antd/input';
import { NzModalModule } from 'ng-zorro-antd/modal';
import { NzSelectModule } from 'ng-zorro-antd/select';
import { NzSpinModule } from 'ng-zorro-antd/spin';

import {
  EstudianteDetalleVeranoView,
  EstudianteEstadoVerano,
  EstudianteSexo,
  EstudianteVeranoUpdatePayload
} from '../../data-access/models/admin-estudiantes.model';

@Component({
  selector: 'app-estudiante-edit-verano-modal',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    NzAlertModule,
    NzButtonModule,
    NzDatePickerModule,
    NzFormModule,
    NzInputModule,
    NzModalModule,
    NzSelectModule,
    NzSpinModule
  ],
  templateUrl: './estudiante-edit-verano-modal.component.html',
  styleUrl: './estudiante-edit-verano-modal.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class EstudianteEditVeranoModalComponent implements OnChanges {
  private readonly formBuilder = inject(FormBuilder);

  @Input() visible = false;
  @Input() detalle: EstudianteDetalleVeranoView | null = null;
  @Input() isLoading = false;
  @Input() isSubmitting = false;
  @Input() errorMessage: string | null = null;

  @Output() cancel = new EventEmitter<void>();
  @Output() confirm = new EventEmitter<EstudianteVeranoUpdatePayload>();

  readonly form = this.formBuilder.nonNullable.group({
    id: [{ value: '', disabled: true }, [Validators.required]],
    estado: ['En proceso' as EstudianteEstadoVerano, [Validators.required]],
    nivel: [''],
    nombreCompleto: ['', [Validators.required]],
    celular: ['', [Validators.required]],
    fechaNacimiento: [null as Date | null, [Validators.required]],
    numeroCasa: [''],
    domicilio: ['', [Validators.required]],
    sexo: ['' as EstudianteSexo | '', [Validators.required]],
    correo: ['', [Validators.required, Validators.email]],
    colegio: ['', [Validators.required]],
    tipoSangre: ['', [Validators.required]],
    alergias: [''],
    contactoNombre: [''],
    contactoTelefono: [''],
    nombreMadre: [''],
    lugarTrabajoMadre: [''],
    telefonoTrabajoMadre: [''],
    celularMadre: [''],
    nombrePadre: [''],
    lugarTrabajoPadre: [''],
    telefonoTrabajoPadre: [''],
    celularPadre: ['']
  });

  ngOnChanges(): void {
    if (!this.detalle) {
      this.form.reset({
        id: { value: '', disabled: true },
        estado: 'En proceso',
        nivel: '',
        nombreCompleto: '',
        celular: '',
        fechaNacimiento: null,
        numeroCasa: '',
        domicilio: '',
        sexo: '',
        correo: '',
        colegio: '',
        tipoSangre: '',
        alergias: '',
        contactoNombre: '',
        contactoTelefono: '',
        nombreMadre: '',
        lugarTrabajoMadre: '',
        telefonoTrabajoMadre: '',
        celularMadre: '',
        nombrePadre: '',
        lugarTrabajoPadre: '',
        telefonoTrabajoPadre: '',
        celularPadre: ''
      });
      return;
    }

    this.form.reset({
      id: { value: this.detalle.id, disabled: true },
      estado: this.detalle.estado,
      nivel: this.detalle.nivel,
      nombreCompleto: this.detalle.nombreCompleto,
      celular: this.detalle.celular,
      fechaNacimiento: this.parseDate(this.detalle.fechaNacimiento),
      numeroCasa: this.detalle.numeroCasa,
      domicilio: this.detalle.domicilio,
      sexo: this.detalle.sexo,
      correo: this.detalle.correo,
      colegio: this.detalle.colegio,
      tipoSangre: this.detalle.tipoSangre,
      alergias: this.detalle.alergias,
      contactoNombre: this.detalle.contactoNombre,
      contactoTelefono: this.detalle.contactoTelefono,
      nombreMadre: this.detalle.nombreMadre,
      lugarTrabajoMadre: this.detalle.lugarTrabajoMadre,
      telefonoTrabajoMadre: this.detalle.telefonoTrabajoMadre,
      celularMadre: this.detalle.celularMadre,
      nombrePadre: this.detalle.nombrePadre,
      lugarTrabajoPadre: this.detalle.lugarTrabajoPadre,
      telefonoTrabajoPadre: this.detalle.telefonoTrabajoPadre,
      celularPadre: this.detalle.celularPadre
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
    const fechaNacimiento = this.formatDate(raw.fechaNacimiento);
    if (!fechaNacimiento) {
      this.form.markAllAsTouched();
      return;
    }

    this.confirm.emit({
      nivel: raw.nivel.trim() || null,
      estado: raw.estado ?? 'En proceso',
      nombre_completo: raw.nombreCompleto.trim(),
      celular: raw.celular.trim(),
      fecha_nacimiento: fechaNacimiento,
      numero_casa: raw.numeroCasa.trim() || null,
      domicilio: raw.domicilio.trim(),
      sexo: raw.sexo as EstudianteSexo,
      correo: raw.correo.trim(),
      colegio: raw.colegio.trim(),
      tipo_sangre: raw.tipoSangre.trim(),
      alergias: raw.alergias.trim() || null,
      contacto_nombre: raw.contactoNombre.trim() || null,
      contacto_telefono: raw.contactoTelefono.trim() || null,
      nombre_madre: raw.nombreMadre.trim() || null,
      lugar_trabajo_madre: raw.lugarTrabajoMadre.trim() || null,
      telefono_trabajo_madre: raw.telefonoTrabajoMadre.trim() || null,
      celular_madre: raw.celularMadre.trim() || null,
      nombre_padre: raw.nombrePadre.trim() || null,
      lugar_trabajo_padre: raw.lugarTrabajoPadre.trim() || null,
      telefono_trabajo_padre: raw.telefonoTrabajoPadre.trim() || null,
      celular_padre: raw.celularPadre.trim() || null
    });
  }

  private parseDate(value: string): Date | null {
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
