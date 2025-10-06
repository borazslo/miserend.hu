import {ChangeDetectionStrategy, Component, inject} from '@angular/core';
import {MAT_DIALOG_DATA, MatDialogClose, MatDialogRef} from '@angular/material/dialog';
import {MatButton, MatIconButton} from '@angular/material/button';
import { FormsModule} from '@angular/forms';
import {MatIcon} from '@angular/material/icon';
import {TranslatePipe, TranslateService} from '@ngx-translate/core';
import {DialogResponse} from '../../enum/dialog-response';
import {DeleteDialogData} from '../church-calendar/church-calendar.component';
import {
  MatCard,
  MatCardActions,
  MatCardContent,
  MatCardHeader,
  MatCardTitle
} from '@angular/material/card';
import {ScriptUtil} from '../../util/script-util';
import {TextUtil} from '../../util/text-util';
import {MassUtil} from '../../util/mass-util';
import {PeriodService} from '../../services/period.service';
import {DateTimeUtil} from '../../util/date-time-util';

@Component({
  selector: 'app-add-message-dialog',
  imports: [
    FormsModule,
    MatButton,
    MatCard,
    MatCardActions,
    MatCardContent,
    MatCardHeader,
    MatCardTitle,
    MatIcon,
    TranslatePipe,
    MatDialogClose,
    MatIconButton,
  ],
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl: './delete-warning-dialog.component.html',
  styleUrls: ['../../../styles.scss','./delete-warning-dialog.component.css']
})
export class DeleteWarningDialogComponent {
  readonly dialogRef = inject(MatDialogRef<DeleteWarningDialogComponent>);
  readonly data = inject<DeleteDialogData>(MAT_DIALOG_DATA);
  public readonly start = DateTimeUtil.getReadableDateTime(this.data.eventData.start);
  public readonly startTime = DateTimeUtil.getReadableTime(this.data.eventData.start);


  constructor(
    readonly periodService: PeriodService,
    readonly translateService: TranslateService
  ) {
  }


  onContinue(): void {
    this.dialogRef.close(DialogResponse.CONTINUE);
  }

  get period(): string {
    const period = this.periodService.getPeriodById(this.data.eventData.mass.periodId);
    return period ? period.name : '';
  }

  get days(): string {
    let days = this.data.eventData.mass.rrule?.byweekday;
    if (ScriptUtil.isNotNull(days)) {
      const translatedDays: string[] = [...days].map(d => this.translateService.instant('DAYS.ON.' + d));
      return TextUtil.concatDays(translatedDays, ', ', this.translateService.instant('SEPARATOR_AND'));
    }
    return '';
  }

  get week(): string | null {
    const rrule = this.data.eventData.mass.rrule;
    if (ScriptUtil.isNull(rrule) || rrule.freq !== 'weekly') {
      return null;
    }

    // Páros / páratlan héten
    if (rrule.byweekno && rrule.byweekno.length > 0) {
      const isEven = rrule.byweekno.every(n => n % 2 === 0);
      const isOdd = rrule.byweekno.every(n => n % 2 === 1);
      const week: string = this.translateService.instant(isEven ? 'RRULE.ON.EVEN' : isOdd ? 'RRULE.ON.ODD' : '');

      if (!isEven && !isOdd) {
        console.error('se nem páros, se nem páratlan heteken ismétlődik...');
        alert('se nem páros, se nem páratlan heteken ismétlődik...');
      }

      return week;
    }

    // Ha az rrule.freq === 'weekly' és nem páros/páratlan, akkor minden héten ismétlődik
    return this.translateService.instant('RRULE.ON.EVERY_WEEK');
  }

  get month(): string | null {
    const rrule = this.data.eventData.mass.rrule;
    if (ScriptUtil.isNotNull(rrule) && ScriptUtil.isNotNull(rrule.bysetpos)) {
      let renumByPos = MassUtil.renumByPos(rrule.bysetpos);
      if(renumByPos != null) {
        return this.translateService.instant('RRULE.ON.' + renumByPos);
      }
    }
    return null;
  }

}
