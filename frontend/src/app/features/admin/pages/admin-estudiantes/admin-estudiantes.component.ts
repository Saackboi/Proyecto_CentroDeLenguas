import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, OnInit, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { Store } from '@ngrx/store';
import { NzButtonModule } from 'ng-zorro-antd/button';
import { NzInputModule } from 'ng-zorro-antd/input';
import { NzSelectModule } from 'ng-zorro-antd/select';
import { NzSpinModule } from 'ng-zorro-antd/spin';
import { NzTableModule } from 'ng-zorro-antd/table';
import { NzTabsModule } from 'ng-zorro-antd/tabs';
import { NzTagModule } from 'ng-zorro-antd/tag';
import { combineLatest, map, startWith } from 'rxjs';

import {
  AdminEstudianteListadoView,
  EstudianteEstadoRegular,
  EstudianteEstadoVerano,
  EstudianteRegularUpdatePayload,
  EstudianteTipo,
  EstudianteVeranoUpdatePayload
} from '../../estudiantes/data-access/models/admin-estudiantes.model';
import { AdminEstudiantesActions } from '../../estudiantes/data-access/store/admin-estudiantes.actions';
import {
  selectAdminEstudiantesDetalleError,
  selectAdminEstudiantesDetalleLoading,
  selectAdminEstudiantesDetalleRegular,
  selectAdminEstudiantesDetalleVerano,
  selectAdminEstudiantesHistorial,
  selectAdminEstudiantesHistorialError,
  selectAdminEstudiantesHistorialLoading,
  selectAdminEstudiantesListado,
  selectAdminEstudiantesListadoError,
  selectAdminEstudiantesListadoLoading,
  selectAdminEstudiantesUpdateRegularError,
  selectAdminEstudiantesUpdateRegularLoading,
  selectAdminEstudiantesUpdateVeranoError,
  selectAdminEstudiantesUpdateVeranoLoading
} from '../../estudiantes/data-access/store/admin-estudiantes.selectors';
import { EstudianteDetalleRegularModalComponent } from '../../estudiantes/ui/estudiante-detalle-regular-modal/estudiante-detalle-regular-modal.component';
import { EstudianteDetalleVeranoModalComponent } from '../../estudiantes/ui/estudiante-detalle-verano-modal/estudiante-detalle-verano-modal.component';
import { EstudianteEditRegularModalComponent } from '../../estudiantes/ui/estudiante-edit-regular-modal/estudiante-edit-regular-modal.component';
import { EstudianteEditVeranoModalComponent } from '../../estudiantes/ui/estudiante-edit-verano-modal/estudiante-edit-verano-modal.component';
import { EstudianteHistorialModalComponent } from '../../estudiantes/ui/estudiante-historial-modal/estudiante-historial-modal.component';

type EstudianteEstadoFiltro = '' | EstudianteEstadoRegular | EstudianteEstadoVerano;

@Component({
  selector: 'app-admin-estudiantes',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterModule,
    NzButtonModule,
    NzInputModule,
    NzSelectModule,
    NzSpinModule,
    NzTableModule,
    NzTabsModule,
    NzTagModule,
    EstudianteDetalleRegularModalComponent,
    EstudianteDetalleVeranoModalComponent,
    EstudianteEditRegularModalComponent,
    EstudianteEditVeranoModalComponent,
    EstudianteHistorialModalComponent
  ],
  templateUrl: './admin-estudiantes.component.html',
  styleUrl: './admin-estudiantes.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AdminEstudiantesComponent implements OnInit {
  private readonly formBuilder = inject(FormBuilder);
  private readonly store = inject(Store);

  readonly filtrosForm = this.formBuilder.nonNullable.group({
    search: '',
    estado: '' as EstudianteEstadoFiltro,
    tipo: 'regular' as EstudianteTipo
  });

  readonly estadoRegularOptions: EstudianteEstadoRegular[] = [
    'Activo',
    'Inactivo',
    'En proceso',
    'En prueba'
  ];
  readonly estadoVeranoOptions: EstudianteEstadoVerano[] = ['Activo', 'Inactivo', 'En proceso'];

  readonly listado$ = this.store.select(selectAdminEstudiantesListado);
  readonly listadoLoading$ = this.store.select(selectAdminEstudiantesListadoLoading);
  readonly listadoError$ = this.store.select(selectAdminEstudiantesListadoError);

  readonly detalleRegular$ = this.store.select(selectAdminEstudiantesDetalleRegular);
  readonly detalleVerano$ = this.store.select(selectAdminEstudiantesDetalleVerano);
  readonly detalleLoading$ = this.store.select(selectAdminEstudiantesDetalleLoading);
  readonly detalleError$ = this.store.select(selectAdminEstudiantesDetalleError);

  readonly updateRegularLoading$ = this.store.select(selectAdminEstudiantesUpdateRegularLoading);
  readonly updateRegularError$ = this.store.select(selectAdminEstudiantesUpdateRegularError);
  readonly updateVeranoLoading$ = this.store.select(selectAdminEstudiantesUpdateVeranoLoading);
  readonly updateVeranoError$ = this.store.select(selectAdminEstudiantesUpdateVeranoError);

  readonly historial$ = this.store.select(selectAdminEstudiantesHistorial);
  readonly historialLoading$ = this.store.select(selectAdminEstudiantesHistorialLoading);
  readonly historialError$ = this.store.select(selectAdminEstudiantesHistorialError);

  readonly tipo$ = this.filtrosForm.controls.tipo.valueChanges.pipe(
    startWith(this.filtrosForm.controls.tipo.value)
  );

  readonly listadoFiltrado$ = combineLatest([
    this.listado$,
    this.filtrosForm.valueChanges.pipe(startWith(this.filtrosForm.getRawValue()))
  ]).pipe(
    map(([listado, filtros]) =>
      this.filterListado(listado, {
        search: filtros.search ?? '',
        estado: (filtros.estado ?? '') as EstudianteEstadoFiltro,
        tipo: (filtros.tipo ?? 'regular') as EstudianteTipo
      })
    )
  );

  selectedId: string | null = null;
  selectedTipo: EstudianteTipo | null = null;

  isDetalleRegularOpen = false;
  isDetalleVeranoOpen = false;
  isEditRegularOpen = false;
  isEditVeranoOpen = false;
  isHistorialOpen = false;

  ngOnInit(): void {
    this.store.dispatch(AdminEstudiantesActions.loadListado());
  }

  onTabChange(index: number): void {
    const tipo: EstudianteTipo = index === 0 ? 'regular' : 'verano';
    this.filtrosForm.patchValue({ tipo, estado: '' });
  }

  onVerDetalle(item: AdminEstudianteListadoView): void {
    this.resetModals();
    this.selectedId = item.id;
    this.selectedTipo = item.tipo;
    this.store.dispatch(AdminEstudiantesActions.clearErrors());
    this.store.dispatch(AdminEstudiantesActions.clearDetalle());
    this.store.dispatch(AdminEstudiantesActions.loadDetalle({ id: item.id, tipo: item.tipo }));
    if (item.tipo === 'regular') {
      this.isDetalleRegularOpen = true;
    } else {
      this.isDetalleVeranoOpen = true;
    }
  }

  onEditar(item: AdminEstudianteListadoView): void {
    this.resetModals();
    this.selectedId = item.id;
    this.selectedTipo = item.tipo;
    this.store.dispatch(AdminEstudiantesActions.clearErrors());
    this.store.dispatch(AdminEstudiantesActions.clearDetalle());
    this.store.dispatch(AdminEstudiantesActions.loadDetalle({ id: item.id, tipo: item.tipo }));
    if (item.tipo === 'regular') {
      this.isEditRegularOpen = true;
    } else {
      this.isEditVeranoOpen = true;
    }
  }

  onVerHistorial(item: AdminEstudianteListadoView): void {
    this.resetModals();
    this.selectedId = item.id;
    this.selectedTipo = item.tipo;
    this.store.dispatch(AdminEstudiantesActions.clearErrors());
    this.store.dispatch(AdminEstudiantesActions.clearHistorial());
    this.store.dispatch(AdminEstudiantesActions.loadHistorial({ id: item.id }));
    this.isHistorialOpen = true;
  }

  onCloseDetalle(): void {
    this.isDetalleRegularOpen = false;
    this.isDetalleVeranoOpen = false;
    this.store.dispatch(AdminEstudiantesActions.clearDetalle());
  }

  onCloseEdit(): void {
    this.isEditRegularOpen = false;
    this.isEditVeranoOpen = false;
    this.store.dispatch(AdminEstudiantesActions.clearErrors());
  }

  onCloseHistorial(): void {
    this.isHistorialOpen = false;
    this.store.dispatch(AdminEstudiantesActions.clearHistorial());
  }

  onConfirmEditRegular(payload: EstudianteRegularUpdatePayload): void {
    if (!this.selectedId) {
      return;
    }
    this.store.dispatch(AdminEstudiantesActions.updateRegular({ id: this.selectedId, payload }));
  }

  onConfirmEditVerano(payload: EstudianteVeranoUpdatePayload): void {
    if (!this.selectedId) {
      return;
    }
    this.store.dispatch(AdminEstudiantesActions.updateVerano({ id: this.selectedId, payload }));
  }

  private filterListado(
    listado: AdminEstudianteListadoView[],
    filtros: { search: string; estado: EstudianteEstadoFiltro; tipo: EstudianteTipo }
  ): AdminEstudianteListadoView[] {
    const search = filtros.search.trim().toLowerCase();
    const estado = filtros.estado;
    const tipo = filtros.tipo;

    return listado.filter((item) => {
      if (item.tipo !== tipo) {
        return false;
      }

      const matchesSearch =
        !search ||
        item.nombre.toLowerCase().includes(search) ||
        item.id.toLowerCase().includes(search);

      const matchesEstado = !estado || item.estado === estado;

      return matchesSearch && matchesEstado;
    });
  }

  private resetModals(): void {
    this.isDetalleRegularOpen = false;
    this.isDetalleVeranoOpen = false;
    this.isEditRegularOpen = false;
    this.isEditVeranoOpen = false;
    this.isHistorialOpen = false;
  }
}
