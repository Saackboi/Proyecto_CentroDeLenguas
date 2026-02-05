export interface DashboardCounts {
  estudiantes: number;
  profesores: number;
  grupos: number;
  solicitudes: number;
}

export interface DatatableResponseDto<T> {
  draw: number;
  recordsTotal: number;
  recordsFiltered: number;
  data: T[];
}

export type UnknownRecord = Record<string, unknown>;
