import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { NzButtonModule } from 'ng-zorro-antd/button';
import { NzDatePickerModule } from 'ng-zorro-antd/date-picker';
import { NzInputModule } from 'ng-zorro-antd/input';
import { NzSelectModule } from 'ng-zorro-antd/select';
import { NzTableModule } from 'ng-zorro-antd/table';
import { NzTabsModule } from 'ng-zorro-antd/tabs';
import { NzTagModule } from 'ng-zorro-antd/tag';
import { combineLatest, map, startWith } from 'rxjs';

import { AdminSolicitudesService } from '../../data-access/services/admin-solicitudes.service';
import { SolicitudUbicacionView } from '../../data-access/models/admin-solicitudes.model';

type SolicitudEstadoFiltro = '' | 'Pendiente' | 'Aceptado' | 'Rechazado' | 'Sin estado';

@Component({
  selector: 'app-admin-solicitudes',
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterModule,
    NzButtonModule,
    NzDatePickerModule,
    NzInputModule,
    NzSelectModule,
    NzTableModule,
    NzTabsModule,
    NzTagModule
  ],
  templateUrl: './admin-solicitudes.component.html',
  styleUrl: './admin-solicitudes.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AdminSolicitudesComponent {
  private readonly formBuilder = inject(FormBuilder);
  private readonly solicitudesService = inject(AdminSolicitudesService);

  readonly getPopupContainer = (trigger: HTMLElement): HTMLElement =>
    trigger.parentElement ?? trigger;

  readonly filtrosForm = this.formBuilder.nonNullable.group({
    search: '',
    estado: '' as SolicitudEstadoFiltro,
    fecha: null as Date | null
  });

  readonly ubicacion$ = this.solicitudesService.getUbicacion();

  readonly ubicacionFiltrada$ = combineLatest([
    this.ubicacion$,
    this.filtrosForm.valueChanges.pipe(startWith(this.filtrosForm.getRawValue()))
  ]).pipe(
    map(([solicitudes, filtros]) => this.filterUbicacion(solicitudes, {
      search: filtros.search ?? '',
      estado: (filtros.estado ?? '') as SolicitudEstadoFiltro,
      fecha: filtros.fecha ?? null
    }))
  );

  onVerComprobante(_solicitud: SolicitudUbicacionView): void {
    return;
  }

  onAprobar(_solicitud: SolicitudUbicacionView): void {
    return;
  }

  onRechazar(_solicitud: SolicitudUbicacionView): void {
    return;
  }

  private filterUbicacion(
    solicitudes: SolicitudUbicacionView[],
    filtros: { search: string; estado: SolicitudEstadoFiltro; fecha: Date | null }
  ): SolicitudUbicacionView[] {
    const search = filtros.search.trim().toLowerCase();
    const estado = filtros.estado;
    const fecha = filtros.fecha ? this.toDateOnly(filtros.fecha) : null;

    return solicitudes.filter((solicitud) => {
      const matchesSearch =
        !search ||
        solicitud.nombreCompleto.toLowerCase().includes(search) ||
        solicitud.correo.toLowerCase().includes(search);

      const matchesEstado = !estado || solicitud.estadoPago === estado;
      const matchesFecha =
        !fecha || this.toDateOnlyLabel(solicitud.fechaRegistro) === fecha;

      return matchesSearch && matchesEstado && matchesFecha;
    });
  }

  private toDateOnly(date: Date): string {
    return date.toISOString().split('T')[0];
  }

  private toDateOnlyLabel(label: string): string {
    const parsed = new Date(label);
    if (Number.isNaN(parsed.getTime())) {
      return '';
    }
    return parsed.toISOString().split('T')[0];
  }
}
