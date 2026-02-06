import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, OnInit, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { Store } from '@ngrx/store';
import { NzButtonModule } from 'ng-zorro-antd/button';
import { NzDatePickerModule } from 'ng-zorro-antd/date-picker';
import { NzInputModule } from 'ng-zorro-antd/input';
import { NzSelectModule } from 'ng-zorro-antd/select';
import { NzSpinModule } from 'ng-zorro-antd/spin';
import { NzTableModule } from 'ng-zorro-antd/table';
import { NzTabsModule } from 'ng-zorro-antd/tabs';
import { NzTagModule } from 'ng-zorro-antd/tag';
import { combineLatest, map, startWith } from 'rxjs';

import {
  SolicitudAbonoView,
  SolicitudUbicacionView,
  SolicitudVeranoView
} from '../../data-access/models/admin-solicitudes.model';
import { AdminSolicitudesActions } from '../../solicitudes/data-access/store/admin-solicitudes.actions';
import {
  selectAdminSolicitudesAbonos,
  selectAdminSolicitudesAbonosError,
  selectAdminSolicitudesAbonosLoading,
  selectAdminSolicitudesUbicacion,
  selectAdminSolicitudesUbicacionError,
  selectAdminSolicitudesUbicacionLoading,
  selectAdminSolicitudesVerano,
  selectAdminSolicitudesVeranoError,
  selectAdminSolicitudesVeranoLoading
} from '../../solicitudes/data-access/store/admin-solicitudes.selectors';

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
    NzSpinModule,
    NzTableModule,
    NzTabsModule,
    NzTagModule
  ],
  templateUrl: './admin-solicitudes.component.html',
  styleUrl: './admin-solicitudes.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AdminSolicitudesComponent implements OnInit {
  private readonly formBuilder = inject(FormBuilder);
  private readonly store = inject(Store);

  readonly getPopupContainer = (trigger: HTMLElement): HTMLElement =>
    trigger.parentElement ?? trigger;

  readonly filtrosForm = this.formBuilder.nonNullable.group({
    search: '',
    estado: '' as SolicitudEstadoFiltro,
    fecha: null as Date | null
  });

  readonly ubicacion$ = this.store.select(selectAdminSolicitudesUbicacion);
  readonly ubicacionLoading$ = this.store.select(selectAdminSolicitudesUbicacionLoading);
  readonly ubicacionError$ = this.store.select(selectAdminSolicitudesUbicacionError);

  readonly abonos$ = this.store.select(selectAdminSolicitudesAbonos);
  readonly abonosLoading$ = this.store.select(selectAdminSolicitudesAbonosLoading);
  readonly abonosError$ = this.store.select(selectAdminSolicitudesAbonosError);

  readonly verano$ = this.store.select(selectAdminSolicitudesVerano);
  readonly veranoLoading$ = this.store.select(selectAdminSolicitudesVeranoLoading);
  readonly veranoError$ = this.store.select(selectAdminSolicitudesVeranoError);

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

  readonly abonosFiltrados$ = combineLatest([
    this.abonos$,
    this.filtrosForm.valueChanges.pipe(startWith(this.filtrosForm.getRawValue()))
  ]).pipe(
    map(([solicitudes, filtros]) => this.filterAbonos(solicitudes, {
      search: filtros.search ?? '',
      estado: (filtros.estado ?? '') as SolicitudEstadoFiltro,
      fecha: filtros.fecha ?? null
    }))
  );

  readonly veranoFiltrado$ = combineLatest([
    this.verano$,
    this.filtrosForm.valueChanges.pipe(startWith(this.filtrosForm.getRawValue()))
  ]).pipe(
    map(([solicitudes, filtros]) => this.filterVerano(solicitudes, {
      search: filtros.search ?? '',
      estado: (filtros.estado ?? '') as SolicitudEstadoFiltro,
      fecha: filtros.fecha ?? null
    }))
  );

  ngOnInit(): void {
    this.store.dispatch(AdminSolicitudesActions.loadUbicacion());
    this.store.dispatch(AdminSolicitudesActions.loadAbonos());
    this.store.dispatch(AdminSolicitudesActions.loadVerano());
  }

  onVerComprobante(_solicitud: SolicitudUbicacionView): void {
    return;
  }

  onAprobar(_solicitud: SolicitudUbicacionView): void {
    return;
  }

  onRechazar(_solicitud: SolicitudUbicacionView): void {
    return;
  }

  onVerComprobanteAbono(_solicitud: SolicitudAbonoView): void {
    return;
  }

  onAprobarAbono(_solicitud: SolicitudAbonoView): void {
    return;
  }

  onRechazarAbono(_solicitud: SolicitudAbonoView): void {
    return;
  }

  onVerComprobanteVerano(_solicitud: SolicitudVeranoView): void {
    return;
  }

  onAprobarVerano(_solicitud: SolicitudVeranoView): void {
    return;
  }

  onRechazarVerano(_solicitud: SolicitudVeranoView): void {
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

  private filterAbonos(
    solicitudes: SolicitudAbonoView[],
    filtros: { search: string; estado: SolicitudEstadoFiltro; fecha: Date | null }
  ): SolicitudAbonoView[] {
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
        !fecha || this.toDateOnlyLabel(solicitud.fechaPago) === fecha;

      return matchesSearch && matchesEstado && matchesFecha;
    });
  }

  private filterVerano(
    solicitudes: SolicitudVeranoView[],
    filtros: { search: string; estado: SolicitudEstadoFiltro; fecha: Date | null }
  ): SolicitudVeranoView[] {
    const search = filtros.search.trim().toLowerCase();
    const estado = filtros.estado;
    const fecha = filtros.fecha ? this.toDateOnly(filtros.fecha) : null;

    return solicitudes.filter((solicitud) => {
      const matchesSearch =
        !search ||
        solicitud.nombreCompleto.toLowerCase().includes(search) ||
        solicitud.correo.toLowerCase().includes(search);

      const matchesEstado = !estado || solicitud.estado === estado;
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
