import { UserDto } from './user.dto';
import { User } from './user.model';

export const mapUserDtoToUser = (dto: UserDto): User => ({
  id: dto.id,
  email: dto.email,
  role: dto.role
});
