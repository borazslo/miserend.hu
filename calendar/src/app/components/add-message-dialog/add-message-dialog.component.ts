import {ChangeDetectionStrategy, Component, inject} from '@angular/core';
import {MAT_DIALOG_DATA, MatDialogModule, MatDialogRef} from '@angular/material/dialog';
import {MatButtonModule} from '@angular/material/button';
import {MatInputModule} from '@angular/material/input';
import { FormsModule, ReactiveFormsModule} from '@angular/forms';
import {MatFormFieldModule} from '@angular/material/form-field';
import {MatMenuModule} from '@angular/material/menu';
import {MatDatepickerModule} from '@angular/material/datepicker';
import {MatTimepickerModule} from '@angular/material/timepicker';
import {MatChipsModule} from '@angular/material/chips';
import {MatIconModule} from '@angular/material/icon';
import {MatAutocompleteModule} from '@angular/material/autocomplete';
import {MatSelectModule} from '@angular/material/select';
import {TranslatePipe, TranslateService} from '@ngx-translate/core';
import {DialogResponse} from '../../enum/dialog-response';
import {MatExpansionModule} from '@angular/material/expansion';
import {MatCard, MatCardHeader, MatCardTitle} from '@angular/material/card';

@Component({
  selector: 'app-add-message-dialog',
  imports: [
    FormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatDatepickerModule,
    MatTimepickerModule,
    MatDialogModule,
    MatMenuModule,
    MatButtonModule,
    MatChipsModule,
    MatIconModule,
    MatAutocompleteModule,
    ReactiveFormsModule,
    MatSelectModule,
    TranslatePipe,
    MatExpansionModule,
    MatCard,
    MatCardHeader,
    MatCardTitle,
  ],
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl: './add-message-dialog.component.html',
  styleUrls: ['../../../styles.scss','./add-message-dialog.component.css']
})
export class AddMessageDialogComponent {
  readonly dialogRef = inject(MatDialogRef<AddMessageDialogComponent>);
  readonly data = inject<{ message: string, decision: boolean }>(MAT_DIALOG_DATA);




  onContinue(): void {
    this.dialogRef.close(DialogResponse.CONTINUE);
  }

}
