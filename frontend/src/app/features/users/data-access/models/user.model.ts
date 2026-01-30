export type UserRole = 'Admin' | 'Profesor' | 'Estudiante';

export interface User {
  id: number;
  email: string;
  role: UserRole;
}
