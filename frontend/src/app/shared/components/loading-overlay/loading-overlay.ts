import { CommonModule } from '@angular/common';
import { Component, Input } from '@angular/core';
import { NzSpinModule } from 'ng-zorro-antd/spin';

@Component({
  selector: 'app-loading-overlay',
  imports: [CommonModule, NzSpinModule],
  templateUrl: './loading-overlay.html',
  styleUrl: './loading-overlay.css',
})
export class LoadingOverlayComponent {
  @Input({ required: true }) isLoading = false;
  @Input() text?: string;
}
