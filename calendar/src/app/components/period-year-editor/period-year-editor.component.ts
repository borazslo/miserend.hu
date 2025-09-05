import {Component, OnInit} from '@angular/core';
import {PeriodService} from '../../services/period.service';
import {MatTab, MatTabGroup} from '@angular/material/tabs';
import {TranslatePipe} from '@ngx-translate/core';
import {MatDatepickerModule} from '@angular/material/datepicker';
import {provideNativeDateAdapter} from '@angular/material/core';
import {MatFormFieldModule} from '@angular/material/form-field';
import {FormsModule} from '@angular/forms';
import {MatButton} from '@angular/material/button';
import {PeriodYearEdit} from '../../model/period-year-edit';
import {Period} from '../../model/period';
import {PeriodYear} from '../../model/period-year';
import {MatInput} from '@angular/material/input';
import {DateTimeUtil} from '../../util/date-time-util';
import {PeriodYearWrapper} from '../../model/period-year-wrapper';
import {SpinnerService} from '../../services/spinner.service';
import {MatSnackBarService} from '../../services/mat-snack-bar.service';

@Component({
  selector: 'app-period-year-editor',
  imports: [
    MatTabGroup,
    MatTab,
    TranslatePipe,
    MatFormFieldModule,
    MatDatepickerModule,
    FormsModule,
    MatButton,
    MatInput,
  ],
  providers: [provideNativeDateAdapter()],
  templateUrl: './period-year-editor.component.html',
  styleUrl: './period-year-editor.component.css'
})
export class PeriodYearEditorComponent {

  public changed: boolean = false;

  public periods: Map<number, Period> = new Map();

  public periodsWrapperMap: Map<number, PeriodYearWrapper> = new Map();

  constructor(
    private readonly spinnerService: SpinnerService,
    private readonly snackBarService: MatSnackBarService,
    private readonly periodService: PeriodService,
  ) {
    this.periodService.periods$.subscribe(periods => {
      if (periods && periods.length > 0) {
        this.periods = new Map(periods.map(item => [item.id, item]));
        this.initPeriods();
      }
    });
  }

  private initPeriods() {
    this.periodService.getPeriodsYear().subscribe(periodsYear => {
      for (const item of periodsYear) {
        const list = this.periodsWrapperMap.get(item.startYear)?.periodYearEdits ?? [];
        const period = this.periods.get(item.periodId);
        if (period) {
          const periodYearEdit: PeriodYearEdit = {
            id: item.id,
            periodId: item.periodId,
            startYear: item.startYear,
            startDate: item.startDate ? new Date(item.startDate) : null,
            endDate: item.endDate ? new Date(item.endDate) : null,
            periodName: period.name,
            multiDay: period.multiDay
          }
          list.push(periodYearEdit);

          const pw: PeriodYearWrapper = {
            year: item.startYear,
            minDate: new Date(item.startYear, 0, 1),
            maxDate: new Date(item.startYear, 11, 31),
            defaultDate: new Date(item.startYear, 5, 1),
            periodYearEdits: list
          }

          this.periodsWrapperMap.set(item.startYear, pw);
        }
      }
    });
  }

  onSavePeriodsYear() {
    const periodYears: PeriodYear[] = Array.from(this.periodsWrapperMap.values())
      .flatMap(wrapper =>
        wrapper.periodYearEdits.map(item => ({
          id: item.id,
          periodId: item.periodId,
          startYear: item.startYear,
          startDate: item.startDate ? DateTimeUtil.getOnlyDateString(item.startDate) : null,
          endDate: item.endDate ? DateTimeUtil.getOnlyDateString(item.endDate) : null,
        }))
      );


    this.periodService.saveData(periodYears).subscribe( x => {
      this.changed = false;
      this.snackBarService.success(x.message);
    });

  }

  onGeneratePeriods() {
    this.periodService.generatePeriods().subscribe( x => {
      this.snackBarService.success(x.message);
    });
  }
}
