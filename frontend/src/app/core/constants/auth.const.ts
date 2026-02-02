export const AUTH_STORAGE_KEYS = {
  accessToken: 'cel_access_token'
} as const;

export const AUTH_API_PATHS = {
  login: '/auth/login',
  me: '/auth/me',
  logout: '/auth/logout'
} as const;

export const AUTH_ROLE_ROUTES = {
  Admin: '/admin',
  Profesor: '/profesor',
  Estudiante: '/estudiante'
} as const;
