import {Component, inject} from '@angular/core';
import {MAT_DIALOG_DATA, MatDialog, MatDialogClose, MatDialogRef} from '@angular/material/dialog';
import {DialogResponse} from '../../enum/dialog-response';
import {
  MatCard,
  MatCardActions,
  MatCardContent,
  MatCardHeader,
  MatCardSubtitle,
  MatCardTitle
} from '@angular/material/card';
import {MatIcon} from '@angular/material/icon';
import {MatButton, MatIconButton} from '@angular/material/button';
import {TranslatePipe, TranslateService} from '@ngx-translate/core';
import {DeleteDialogData, EventViewerDialogData} from '../church-calendar/church-calendar.component';
import {DateTimeUtil} from '../../util/date-time-util';
import {MatMenu, MatMenuItem, MatMenuTrigger} from '@angular/material/menu';
import {PeriodService} from '../../services/period.service';
import {TextUtil} from '../../util/text-util';
import {MassUtil} from '../../util/mass-util';
import {ScriptUtil} from '../../util/script-util';
import {MatTooltip} from '@angular/material/tooltip';
import {DeleteWarningDialogComponent} from '../delete-warning-dialog/delete-warning-dialog.component';
import {SpecialType} from "../../model/period";

@Component({
  selector: 'app-event-viewer-dialog',
  imports: [
    MatButton,
    MatCard,
    MatCardActions,
    MatCardContent,
    MatCardHeader,
    MatCardTitle,
    MatCardSubtitle,
    MatIcon,
    TranslatePipe,
    MatMenu,
    MatMenuItem,
    MatMenuTrigger,
    MatDialogClose,
    MatTooltip,
    MatIconButton,
  ],
  templateUrl: './event-viewer-dialog.component.html',
  styleUrls: ['../../../styles.scss','./event-viewer-dialog.component.css']
})
export class EventViewerDialogComponent {

  readonly dialogRef = inject(MatDialogRef<EventViewerDialogComponent>);
  readonly dialog = inject(MatDialog);
  readonly data = inject<EventViewerDialogData>(MAT_DIALOG_DATA);

  public readonly start = DateTimeUtil.getReadableDateTime(this.data.start);

  constructor(
    readonly periodService: PeriodService,
    readonly translateService: TranslateService
  ) {
  }

  onDeleteMassOne(): void {
    const deleteDialogData: DeleteDialogData = {eventData: this.data, deleteOne: true};
    const messageDialogRef = this.dialog.open(DeleteWarningDialogComponent, {
      data: deleteDialogData
    });

    messageDialogRef.afterClosed().subscribe(result => {
      if (result === DialogResponse.CONTINUE) {
        this.dialogRef.close(DialogResponse.DELETE_ONE);
      }
    });
  }

  onDeleteMassAll(): void {
    const deleteDialogData: DeleteDialogData = {eventData: this.data, deleteOne: false};
    const messageDialogRef = this.dialog.open(DeleteWarningDialogComponent, {
      data: deleteDialogData
    });

    messageDialogRef.afterClosed().subscribe(result => {
      if (result === DialogResponse.CONTINUE) {
        this.dialogRef.close(DialogResponse.DELETE_ALL);
      }
    });
  }

  onEditMassAll(): void {
    this.dialogRef.close(DialogResponse.EVENT_VIEWER_EDIT_ALL);
  }

  onEditMassOne(): void {
    this.dialogRef.close(DialogResponse.EVENT_VIEWER_EDIT_ONE);
  }

  get period(): string {
    const period = this.periodService.getPeriodById(this.data.mass.periodId);
    return period ? period.name : '';
  }

  get days(): string {
    let days = this.data.mass.rrule?.byweekday;
    if (ScriptUtil.isNotNull(days)) {
      const translatedDays: string[] = [...days].map(d => this.translateService.instant('DAYS.ON.' + d));
      return TextUtil.concatDays(translatedDays, ', ', this.translateService.instant('SEPARATOR_AND'));
    }
    return '';
  }

  get week(): string | null {
    const rrule = this.data.mass.rrule;
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
    const rrule = this.data.mass.rrule;
    if (ScriptUtil.isNotNull(rrule) && ScriptUtil.isNotNull(rrule.bysetpos)) {
      let renumByPos = MassUtil.renumByPos(rrule.bysetpos);
      if(renumByPos != null) {
        return this.translateService.instant('RRULE.ON.' + renumByPos);
      }
    }
    return null;
  }

  get christmas(): string | null {
    const rrule = this.data.mass.rrule;
    if (ScriptUtil.isNotNull(rrule) && rrule.bymonth === 12 && ScriptUtil.isNotNull(rrule.bymonthday)) {
      let christmasDay = MassUtil.christmasDayByMonthday(rrule.bymonthday);
      if(christmasDay != null) {
        return this.translateService.instant("CHRISTMAS_DAYS." + christmasDay);
      }
    }
    return null;
  }

  get easter(): string | null {
    if (ScriptUtil.isNotNull(this.data.mass.periodId)) {
      const specialPeriodType = this.periodService.getSpecialPeriodType(this.data.mass.periodId);
      if (specialPeriodType === SpecialType.EASTER) {
        const rrule = this.data.mass.rrule;
        if (ScriptUtil.isNotNull(rrule) && ScriptUtil.isNotNull(rrule.byweekday) && rrule.byweekday.length === 1) {
          let easterDay = rrule.byweekday[0];
          if(easterDay != null) {
            return this.translateService.instant("EASTER_DAYS." + easterDay);
          }
        }
      }
    }
    return null;
  }
}
