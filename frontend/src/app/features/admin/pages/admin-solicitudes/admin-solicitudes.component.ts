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
  SolicitudAbonoApprovalPayload,
  SolicitudRechazoPayload,
  SolicitudUbicacionApprovalPayload,
  SolicitudUbicacionView,
  SolicitudVeranoApprovalPayload,
  SolicitudVeranoView
} from '../../data-access/models/admin-solicitudes.model';
import { AdminSolicitudesActions } from '../../solicitudes/data-access/store/admin-solicitudes.actions';
import {
  selectAdminSolicitudesAbonos,
  selectAdminSolicitudesAbonosError,
  selectAdminSolicitudesAbonosLoading,
  selectAdminSolicitudesAbonoSaldo,
  selectAdminSolicitudesAbonoSaldoError,
  selectAdminSolicitudesAbonoSaldoLoading,
  selectAdminSolicitudesApproveAbonoError,
  selectAdminSolicitudesApproveAbonoLoading,
  selectAdminSolicitudesApproveUbicacionError,
  selectAdminSolicitudesApproveUbicacionLoading,
  selectAdminSolicitudesApproveVeranoError,
  selectAdminSolicitudesApproveVeranoLoading,
  selectAdminSolicitudesRejectAbonoError,
  selectAdminSolicitudesRejectAbonoLoading,
  selectAdminSolicitudesRejectUbicacionError,
  selectAdminSolicitudesRejectUbicacionLoading,
  selectAdminSolicitudesRejectVeranoError,
  selectAdminSolicitudesRejectVeranoLoading,
  selectAdminSolicitudesUbicacion,
  selectAdminSolicitudesUbicacionError,
  selectAdminSolicitudesUbicacionLoading,
  selectAdminSolicitudesVerano,
  selectAdminSolicitudesVeranoError,
  selectAdminSolicitudesVeranoLoading
} from '../../solicitudes/data-access/store/admin-solicitudes.selectors';
import { SolicitudApproveAbonoModalComponent } from '../../solicitudes/ui/solicitud-approve-abono-modal/solicitud-approve-abono-modal.component';
import { SolicitudApproveUbicacionModalComponent } from '../../solicitudes/ui/solicitud-approve-ubicacion-modal/solicitud-approve-ubicacion-modal.component';
import { SolicitudApproveVeranoModalComponent } from '../../solicitudes/ui/solicitud-approve-verano-modal/solicitud-approve-verano-modal.component';
import {
  ComprobanteImagen,
  SolicitudComprobanteModalComponent
} from '../../solicitudes/ui/solicitud-comprobante-modal/solicitud-comprobante-modal.component';
import { SolicitudRejectModalComponent } from '../../solicitudes/ui/solicitud-reject-modal/solicitud-reject-modal.component';

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
    NzTagModule,
    SolicitudApproveAbonoModalComponent,
    SolicitudApproveUbicacionModalComponent,
    SolicitudApproveVeranoModalComponent,
    SolicitudComprobanteModalComponent,
    SolicitudRejectModalComponent
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

  readonly approveUbicacionLoading$ = this.store.select(
    selectAdminSolicitudesApproveUbicacionLoading
  );
  readonly rejectUbicacionLoading$ = this.store.select(
    selectAdminSolicitudesRejectUbicacionLoading
  );
  readonly approveAbonoLoading$ = this.store.select(selectAdminSolicitudesApproveAbonoLoading);
  readonly rejectAbonoLoading$ = this.store.select(selectAdminSolicitudesRejectAbonoLoading);
  readonly approveVeranoLoading$ = this.store.select(selectAdminSolicitudesApproveVeranoLoading);
  readonly rejectVeranoLoading$ = this.store.select(selectAdminSolicitudesRejectVeranoLoading);
  readonly abonoSaldo$ = this.store.select(selectAdminSolicitudesAbonoSaldo);
  readonly abonoSaldoLoading$ = this.store.select(selectAdminSolicitudesAbonoSaldoLoading);
  readonly abonoSaldoError$ = this.store.select(selectAdminSolicitudesAbonoSaldoError);
  readonly approveUbicacionError$ = this.store.select(selectAdminSolicitudesApproveUbicacionError);
  readonly rejectUbicacionError$ = this.store.select(selectAdminSolicitudesRejectUbicacionError);
  readonly approveAbonoError$ = this.store.select(selectAdminSolicitudesApproveAbonoError);
  readonly rejectAbonoError$ = this.store.select(selectAdminSolicitudesRejectAbonoError);
  readonly approveVeranoError$ = this.store.select(selectAdminSolicitudesApproveVeranoError);
  readonly rejectVeranoError$ = this.store.select(selectAdminSolicitudesRejectVeranoError);

  selectedUbicacion: SolicitudUbicacionView | null = null;
  selectedAbono: SolicitudAbonoView | null = null;
  selectedVerano: SolicitudVeranoView | null = null;

  isUbicacionModalOpen = false;
  isAbonoModalOpen = false;
  isVeranoModalOpen = false;
  isRejectModalOpen = false;
  isComprobanteModalOpen = false;

  rejectContext: { type: 'ubicacion' | 'abono' | 'verano'; id: string } | null = null;
  rejectTitle = 'Rechazar solicitud';
  rejectDescription = 'Indica el motivo del rechazo.';

  comprobanteTitle = 'Comprobante';
  comprobanteImages: ComprobanteImagen[] = [];

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

  onVerComprobante(solicitud: SolicitudUbicacionView): void {
    this.store.dispatch(AdminSolicitudesActions.clearUbicacionErrors());
    this.comprobanteTitle = `Comprobante ubicacion · ${solicitud.nombreCompleto}`;
    this.comprobanteImages = [
      {
        label: 'Comprobante de ubicacion',
        url: solicitud.comprobanteUrl
      }
    ];
    this.isComprobanteModalOpen = true;
  }

  onAprobar(solicitud: SolicitudUbicacionView): void {
    this.store.dispatch(AdminSolicitudesActions.clearUbicacionErrors());
    this.selectedUbicacion = solicitud;
    this.isUbicacionModalOpen = true;
  }

  onRechazar(solicitud: SolicitudUbicacionView): void {
    this.store.dispatch(AdminSolicitudesActions.clearUbicacionErrors());
    this.rejectContext = { type: 'ubicacion', id: solicitud.id };
    this.rejectTitle = `Rechazar ubicacion · ${solicitud.nombreCompleto}`;
    this.rejectDescription = 'Indica el motivo para rechazar esta solicitud de ubicacion.';
    this.isRejectModalOpen = true;
  }

  onVerComprobanteAbono(solicitud: SolicitudAbonoView): void {
    this.store.dispatch(AdminSolicitudesActions.clearAbonoErrors());
    this.comprobanteTitle = `Comprobante abono · ${solicitud.nombreCompleto}`;
    this.comprobanteImages = [
      {
        label: 'Comprobante de abono',
        url: solicitud.comprobanteUrl
      }
    ];
    this.isComprobanteModalOpen = true;
  }

  onAprobarAbono(solicitud: SolicitudAbonoView): void {
    this.store.dispatch(AdminSolicitudesActions.clearAbonoErrors());
    this.selectedAbono = solicitud;
    this.isAbonoModalOpen = true;
    this.store.dispatch(AdminSolicitudesActions.loadAbonoSaldo({ idEstudiante: solicitud.idEstudiante }));
  }

  onRechazarAbono(solicitud: SolicitudAbonoView): void {
    this.store.dispatch(AdminSolicitudesActions.clearAbonoErrors());
    this.rejectContext = { type: 'abono', id: solicitud.idEstudiante };
    this.rejectTitle = `Rechazar abono · ${solicitud.nombreCompleto}`;
    this.rejectDescription = 'Indica el motivo para rechazar este abono.';
    this.isRejectModalOpen = true;
  }

  onVerComprobanteVerano(solicitud: SolicitudVeranoView): void {
    this.store.dispatch(AdminSolicitudesActions.clearVeranoErrors());
    this.comprobanteTitle = `Documentos verano · ${solicitud.nombreCompleto}`;
    this.comprobanteImages = [
      {
        label: 'Firma familiar',
        url: solicitud.firmaFamiliarUrl
      },
      {
        label: 'Cedula familiar',
        url: solicitud.cedulaFamiliarUrl
      },
      {
        label: 'Cedula estudiante',
        url: solicitud.cedulaEstudianteUrl
      }
    ];
    this.isComprobanteModalOpen = true;
  }

  onAprobarVerano(solicitud: SolicitudVeranoView): void {
    this.store.dispatch(AdminSolicitudesActions.clearVeranoErrors());
    this.selectedVerano = solicitud;
    this.isVeranoModalOpen = true;
  }

  onRechazarVerano(solicitud: SolicitudVeranoView): void {
    this.store.dispatch(AdminSolicitudesActions.clearVeranoErrors());
    this.rejectContext = { type: 'verano', id: solicitud.id };
    this.rejectTitle = `Rechazar verano · ${solicitud.nombreCompleto}`;
    this.rejectDescription = 'Indica el motivo para rechazar esta solicitud de verano.';
    this.isRejectModalOpen = true;
  }

  onCloseUbicacionModal(): void {
    this.isUbicacionModalOpen = false;
    this.selectedUbicacion = null;
    this.store.dispatch(AdminSolicitudesActions.clearUbicacionErrors());
  }

  onCloseAbonoModal(): void {
    this.isAbonoModalOpen = false;
    this.selectedAbono = null;
    this.store.dispatch(AdminSolicitudesActions.clearAbonoSaldo());
    this.store.dispatch(AdminSolicitudesActions.clearAbonoErrors());
  }

  onCloseVeranoModal(): void {
    this.isVeranoModalOpen = false;
    this.selectedVerano = null;
    this.store.dispatch(AdminSolicitudesActions.clearVeranoErrors());
  }

  onCloseRejectModal(): void {
    this.isRejectModalOpen = false;
    this.rejectContext = null;
    this.store.dispatch(AdminSolicitudesActions.clearUbicacionErrors());
    this.store.dispatch(AdminSolicitudesActions.clearAbonoErrors());
    this.store.dispatch(AdminSolicitudesActions.clearVeranoErrors());
  }

  onCloseComprobanteModal(): void {
    this.isComprobanteModalOpen = false;
    this.comprobanteImages = [];
  }

  onConfirmUbicacionApproval(payload: SolicitudUbicacionApprovalPayload): void {
    this.store.dispatch(AdminSolicitudesActions.approveUbicacion({ payload }));
    this.onCloseUbicacionModal();
  }

  onConfirmVeranoApproval(payload: SolicitudVeranoApprovalPayload): void {
    this.store.dispatch(AdminSolicitudesActions.approveVerano({ payload }));
    this.onCloseVeranoModal();
  }

  onConfirmAbonoApproval(payload: SolicitudAbonoApprovalPayload): void {
    this.store.dispatch(AdminSolicitudesActions.approveAbono({ payload }));
    this.onCloseAbonoModal();
  }

  onConfirmRechazo(motivo: string): void {
    if (!this.rejectContext) {
      return;
    }

    const payload: SolicitudRechazoPayload = {
      id_estudiante: this.rejectContext.id,
      motivo
    };

    if (this.rejectContext.type === 'ubicacion') {
      this.store.dispatch(AdminSolicitudesActions.rejectUbicacion({ payload }));
    } else if (this.rejectContext.type === 'abono') {
      this.store.dispatch(AdminSolicitudesActions.rejectAbono({ payload }));
    } else {
      this.store.dispatch(AdminSolicitudesActions.rejectVerano({ payload }));
    }

    this.onCloseRejectModal();
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
