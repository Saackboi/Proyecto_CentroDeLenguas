import { AuthMeDto } from '../../models/auth.dto';

export interface AuthState {
  isAuthenticated: boolean;
  user: AuthMeDto | null;
  role: AuthMeDto['role'] | null;
  isLoading: boolean;
  error: string | null;
}

export const initialAuthState: AuthState = {
  isAuthenticated: false,
  user: null,
  role: null,
  isLoading: false,
  error: null
};
