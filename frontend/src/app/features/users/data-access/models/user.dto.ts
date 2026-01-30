export interface UserDto {
  id: number;
  email: string;
  role: 'Admin' | 'Profesor' | 'Estudiante';
}
