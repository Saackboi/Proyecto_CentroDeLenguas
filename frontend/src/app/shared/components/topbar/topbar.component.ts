import { CommonModule } from '@angular/common';
import { Component, Input } from '@angular/core';
import { RouterModule } from '@angular/router';

export interface TopbarLink {
  id: string;
  label: string;
  path: string;
  fragment?: string;
}

@Component({
  selector: 'app-topbar',
  imports: [CommonModule, RouterModule],
  templateUrl: './topbar.component.html',
  styleUrl: './topbar.component.css'
})
export class TopbarComponent {
  @Input({ required: true }) links: TopbarLink[] = [];
  @Input() activeSection = '';
}
