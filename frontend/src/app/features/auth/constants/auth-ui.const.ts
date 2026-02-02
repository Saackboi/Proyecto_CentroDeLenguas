export const LOGIN_QUERY_PARAMS = {
  status: 'status'
} as const;

export const LOGIN_STATUS = {
  roleUnavailable: 'role_unavailable',
  logoutError: 'logout_error'
} as const;

export const LOGIN_STATUS_MESSAGES = {
  roleUnavailable: 'Vista no implementada aún',
  logoutError: 'No se pudo cerrar la sesión'
} as const;

export const LOGIN_ERROR_MESSAGES = {
  invalidCredentials: 'Credenciales invalidas'
} as const;
