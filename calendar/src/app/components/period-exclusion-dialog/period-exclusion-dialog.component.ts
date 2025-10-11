import {Component, Inject} from '@angular/core';
import {
  MAT_DIALOG_DATA,
  MatDialogActions,
  MatDialogContent,
  MatDialogRef,
  MatDialogTitle
} from '@angular/material/dialog';
import {TranslatePipe} from '@ngx-translate/core';
import {MatButton} from '@angular/material/button';

export interface PeriodExclusionDialogData {
  periodName: string;
  recentlyExcludedPeriodNames: string[];
  recentlyExclusionSourcePeriodNames: string[];
}

@Component({
  selector: 'app-period-exclusion-dialog',
  imports: [
    MatDialogTitle,
    MatDialogContent,
    TranslatePipe,
    MatDialogActions,
    MatButton
  ],
  templateUrl: './period-exclusion-dialog.component.html',
  styleUrl: './period-exclusion-dialog.component.css'
})
export class PeriodExclusionDialogComponent {
  constructor(
    public dialogRef: MatDialogRef<PeriodExclusionDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: PeriodExclusionDialogData
  ) {}

  close(): void {
    this.dialogRef.close();
  }
}
