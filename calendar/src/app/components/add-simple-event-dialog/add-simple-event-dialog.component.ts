import {Component, inject} from '@angular/core';
import {MatIconModule} from '@angular/material/icon';
import {MatCardModule} from '@angular/material/card';
import {MatButtonModule} from '@angular/material/button';
import {SimpleDialogData} from '../church-calendar/church-calendar.component';
import {MAT_DIALOG_DATA, MatDialogClose, MatDialogRef} from '@angular/material/dialog';
import {DateTimeUtil} from '../../util/date-time-util';
import {DialogResponse} from '../../enum/dialog-response';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
  selector: 'app-add-simple-event-dialog',
  imports: [
    MatIconModule,
    MatCardModule,
    MatButtonModule,
    TranslatePipe,
    MatDialogClose
  ],
  templateUrl: './add-simple-event-dialog.component.html',
  styleUrls: ['../../../styles.scss', './add-simple-event-dialog.component.css']
})
export class AddSimpleEventDialogComponent {

  readonly dialogRef = inject(MatDialogRef<AddSimpleEventDialogComponent>);
  readonly data = inject<SimpleDialogData>(MAT_DIALOG_DATA);
  readonly dateTime: string;
  readonly title: string;

  constructor() {
    this.dateTime = DateTimeUtil.getDateTimeString(this.data.dateTime);
    this.title = this.data.title;
  }

  onSaveSimple(): void {
    this.dialogRef.close(DialogResponse.SAVE_SIMPLE);
  }
  onMoreDetails(): void {
    this.dialogRef.close(DialogResponse.MORE_DETAILS);
  }

}
