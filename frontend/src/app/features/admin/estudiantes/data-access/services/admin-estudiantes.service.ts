import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';

import { environment } from '../../../../../../environments/environment';
import { ApiResponseDto } from '../../../../../core/models/api-response.dto';
import {
  AdminEstudianteListadoDto,
  AdminEstudianteListadoView,
  EstudianteDetalleRegularDto,
  EstudianteDetalleResponseDto,
  EstudianteDetalleRegularView,
  EstudianteDetalleVeranoDto,
  EstudianteDetalleVeranoView,
  EstudianteHistorialDto,
  EstudianteHistorialMovimientoDto,
  EstudianteHistorialMovimientoView,
  EstudianteHistorialPagoDto,
  EstudianteHistorialPagoView,
  EstudianteHistorialView,
  EstudianteRegularUpdatePayload,
  EstudianteTipo,
  EstudianteVeranoUpdatePayload
} from '../models/admin-estudiantes.model';

@Injectable({ providedIn: 'root' })
export class AdminEstudiantesService {
  private readonly baseUrl = environment.apiBaseUrl;

  constructor(private readonly http: HttpClient) {}

  getListado(): Observable<AdminEstudianteListadoView[]> {
    return this.http
      .get<ApiResponseDto<AdminEstudianteListadoDto[]>>(
        `${this.baseUrl}/admin/dashboard/estudiantes`
      )
      .pipe(map((response) => (response.data ?? []).map((item) => this.mapListado(item))));
  }

  getDetalle(id: string, tipo: EstudianteTipo): Observable<{
    tipo: EstudianteTipo;
    data: EstudianteDetalleRegularView | EstudianteDetalleVeranoView;
  }> {
    return this.http
      .get<ApiResponseDto<EstudianteDetalleResponseDto>>(
        `${this.baseUrl}/admin/estudiantes/${id}?tipo=${tipo}`
      )
      .pipe(
        map((response) => {
          const data = response.data;
          if (!data) {
            throw new Error('detalle_vacio');
          }
          return data.tipo === 'verano'
            ? { tipo: 'verano', data: this.mapDetalleVerano(data.data as EstudianteDetalleVeranoDto) }
            : { tipo: 'regular', data: this.mapDetalleRegular(data.data as EstudianteDetalleRegularDto) };
        })
      );
  }

  updateRegular(id: string, payload: EstudianteRegularUpdatePayload): Observable<void> {
    return this.http
      .patch<ApiResponseDto<unknown>>(`${this.baseUrl}/admin/estudiantes/${id}`, payload)
      .pipe(map(() => undefined));
  }

  updateVerano(id: string, payload: EstudianteVeranoUpdatePayload): Observable<void> {
    return this.http
      .patch<ApiResponseDto<unknown>>(`${this.baseUrl}/admin/estudiantes-verano/${id}`, payload)
      .pipe(map(() => undefined));
  }

  getHistorial(id: string): Observable<EstudianteHistorialView> {
    return this.http
      .get<ApiResponseDto<EstudianteHistorialDto>>(
        `${this.baseUrl}/admin/estudiantes/${id}/historial`
      )
      .pipe(
        map((response) => {
          const data = response.data ?? { movimientos: [], pagos: [] };
          return {
            movimientos: data.movimientos.map((item) => this.mapMovimiento(item)),
            pagos: data.pagos.map((item) => this.mapPago(item))
          };
        })
      );
  }

  private mapListado(item: AdminEstudianteListadoDto): AdminEstudianteListadoView {
    return {
      id: item.id_estudiante,
      nombre: item.estudiante ?? `ID ${item.id_estudiante}`,
      nivel: item.nivel ?? '--',
      profesor: item.profesor ?? '--',
      estado: item.estado ?? 'Sin estado',
      tipo: item.tipo,
      tipoLabel: item.tipo === 'verano' ? 'Verano' : 'Regular'
    };
  }

  private mapDetalleRegular(data: EstudianteDetalleRegularDto): EstudianteDetalleRegularView {
    return {
      id: data.id_estudiante,
      tipoId: data.tipo_id ?? '',
      nombre: data.nombre ?? '',
      apellido: data.apellido ?? '',
      correoPersonal: data.correo_personal ?? '',
      correoUtp: data.correo_utp ?? '',
      telefono: data.telefono ?? '',
      nivel: data.nivel ?? '',
      estado: (data.estado ?? 'Activo') as EstudianteDetalleRegularView['estado'],
      esEstudiante: (data.es_estudiante ?? 'NO') as EstudianteDetalleRegularView['esEstudiante'],
      saldoPendiente: Number(data.saldo_pendiente ?? 0),
      deudaTotal: Number(data.deuda_total ?? 0)
    };
  }

  private mapDetalleVerano(data: EstudianteDetalleVeranoDto): EstudianteDetalleVeranoView {
    return {
      id: data.id_estudiante,
      nombreCompleto: data.nombre_completo ?? '',
      estado: (data.estado ?? 'En proceso') as EstudianteDetalleVeranoView['estado'],
      nivel: data.nivel ?? '',
      celular: data.celular ?? '',
      fechaNacimiento: data.fecha_nacimiento ?? '',
      numeroCasa: data.numero_casa ?? '',
      domicilio: data.domicilio ?? '',
      sexo: data.sexo ?? '',
      correo: data.correo ?? '',
      colegio: data.colegio ?? '',
      alergias: data.alergias ?? '',
      tipoSangre: data.tipo_sangre ?? '',
      contactoNombre: data.contacto_nombre ?? '',
      contactoTelefono: data.contacto_telefono ?? '',
      nombrePadre: data.nombre_padre ?? '',
      lugarTrabajoPadre: data.lugar_trabajo_padre ?? '',
      telefonoTrabajoPadre: data.telefono_trabajo_padre ?? '',
      celularPadre: data.celular_padre ?? '',
      nombreMadre: data.nombre_madre ?? '',
      lugarTrabajoMadre: data.lugar_trabajo_madre ?? '',
      telefonoTrabajoMadre: data.telefono_trabajo_madre ?? '',
      celularMadre: data.celular_madre ?? ''
    };
  }

  private mapMovimiento(item: EstudianteHistorialMovimientoDto): EstudianteHistorialMovimientoView {
    const tipo =
      item.movement_type === 'charge'
        ? 'Cargo'
        : item.movement_type === 'payment'
          ? 'Abono'
          : 'Ajuste';

    return {
      id: item.id,
      tipo,
      motivo: item.reason || '--',
      monto: this.formatMoney(item.amount),
      fecha: this.formatDate(item.created_at),
      referencia: item.payment_id
        ? `Pago #${item.payment_id}`
        : item.group_session_id
          ? `Grupo ${item.group_session_id}`
          : '--'
    };
  }

  private mapPago(item: EstudianteHistorialPagoDto): EstudianteHistorialPagoView {
    return {
      id: item.id_pago,
      tipo: item.payment_type === 'PruebaUbicacion' ? 'Prueba ubicacion' : 'Abono',
      metodo: item.method ?? 'Sin metodo',
      banco: item.bank ?? 'Sin banco',
      propietario: item.account_owner ?? 'Sin propietario',
      monto: this.formatMoney(item.amount),
      fechaPago: item.paid_at ? this.formatDate(item.paid_at) : 'Sin fecha',
      estado: item.status ?? 'Sin estado',
      comprobante: this.resolveAssetUrl(item.receipt_path)
    };
  }

  private formatDate(value: string | null): string {
    if (!value) {
      return '--';
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
      return value;
    }
    return date.toLocaleDateString('es-PA', {
      year: 'numeric',
      month: 'short',
      day: '2-digit'
    });
  }

  private formatMoney(value: number | null): string {
    const amount = typeof value === 'number' ? value : Number(value ?? 0);
    if (Number.isNaN(amount)) {
      return 'B/.0.00';
    }
    return `B/.${amount.toFixed(2)}`;
  }

  private resolveAssetUrl(path: string | null): string | null {
    if (!path) {
      return null;
    }
    if (path.startsWith('http://') || path.startsWith('https://')) {
      return path;
    }
    const base = this.baseUrl.replace(/\/api\/?$/, '');
    if (path.startsWith('/')) {
      return `${base}${path}`;
    }
    return `${base}/${path}`;
  }
}
