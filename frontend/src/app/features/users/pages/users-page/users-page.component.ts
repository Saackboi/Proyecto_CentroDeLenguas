import { ChangeDetectionStrategy, Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Store } from '@ngrx/store';
import { NzSpinModule } from 'ng-zorro-antd/spin';

import { UsersActions } from '../../data-access/store/users.actions';
import {
  selectUsers,
  selectUsersError,
  selectUsersLoading
} from '../../data-access/store/users.selectors';
import { UsersTableComponent } from '../../ui/users-table/users-table.component';

@Component({
  selector: 'app-users-page',
  imports: [CommonModule, NzSpinModule, UsersTableComponent],
  templateUrl: './users-page.component.html',
  styleUrl: './users-page.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class UsersPageComponent implements OnInit {
  private readonly store = inject(Store);

  readonly users$ = this.store.select(selectUsers);
  readonly isLoading$ = this.store.select(selectUsersLoading);
  readonly error$ = this.store.select(selectUsersError);

  ngOnInit(): void {
    this.store.dispatch(UsersActions.loadUsers());
  }
}
