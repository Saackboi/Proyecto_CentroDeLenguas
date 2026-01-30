import { UserDto } from '../models/user.dto';

export const USERS_API_PATHS = {
  me: '/auth/me'
} as const;

export const USERS_ERROR_MESSAGES = {
  loadFailed: 'No fue posible cargar usuarios.'
} as const;

export const USER_MOCKS: UserDto[] = [
  { id: 1, email: 'admin@cel.local', role: 'Admin' },
  { id: 2, email: 'profesor@cel.local', role: 'Profesor' },
  { id: 3, email: 'estudiante@cel.local', role: 'Estudiante' }
];
