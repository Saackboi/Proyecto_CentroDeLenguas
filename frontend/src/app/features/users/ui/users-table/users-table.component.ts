import { ChangeDetectionStrategy, Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NzTableModule } from 'ng-zorro-antd/table';

import { User } from '../../data-access/models/user.model';

@Component({
  selector: 'app-users-table',
  imports: [CommonModule, NzTableModule],
  templateUrl: './users-table.component.html',
  styleUrl: './users-table.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class UsersTableComponent {
  @Input({ required: true }) users: User[] = [];

  readonly trackById = (_: number, user: User) => user.id;
}
