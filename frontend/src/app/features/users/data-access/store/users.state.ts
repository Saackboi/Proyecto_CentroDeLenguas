import { User } from '../models/user.model';

export interface UsersState {
  users: User[];
  isLoading: boolean;
  error: string | null;
}

export const initialUsersState: UsersState = {
  users: [],
  isLoading: false,
  error: null
};
