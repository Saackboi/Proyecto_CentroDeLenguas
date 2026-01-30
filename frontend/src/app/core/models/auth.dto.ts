export interface LoginRequestDto {
  correo: string;
  contrasena: string;
}

export interface AuthTokenDto {
  access_token: string;
  token_type: string;
  expires_in: number;
}
