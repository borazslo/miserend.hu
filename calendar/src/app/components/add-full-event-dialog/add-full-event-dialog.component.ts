import {ChangeDetectionStrategy, Component, inject} from '@angular/core';
import {MAT_DIALOG_DATA, MatDialogModule, MatDialogRef} from '@angular/material/dialog';
import {MatButtonModule} from '@angular/material/button';
import {DialogData} from '../church-calendar/church-calendar.component';
import {MatInputModule} from '@angular/material/input';
import {FormControl, FormsModule, ReactiveFormsModule} from '@angular/forms';
import {MatFormFieldModule} from '@angular/material/form-field';
import {MatMenuModule} from '@angular/material/menu';
import {MatDatepickerModule} from '@angular/material/datepicker';
import {MatTimepickerModule} from '@angular/material/timepicker';
import {provideNativeDateAdapter} from '@angular/material/core';
import {MatChipsModule} from '@angular/material/chips';
import {MatIconModule} from '@angular/material/icon';
import {MatAutocompleteModule} from '@angular/material/autocomplete';
import {Day} from '../../enum/day';
import {MatTooltip} from '@angular/material/tooltip';
import {PeriodService} from '../../services/period.service';
import {AsyncPipe, TitleCasePipe, CommonModule} from '@angular/common';
import {map, Observable, of, startWith} from 'rxjs';
import {MatSelectModule} from '@angular/material/select';
import {recurrences, Renum} from '../../enum/recurrence';
import {TranslatePipe, TranslateService} from '@ngx-translate/core';
import {Rite, RiteMassTypes} from '../../model/mass';
import {MassUtil} from '../../util/mass-util';
import {LanguageCode} from '../../enum/language-code';
import {DialogResponse} from '../../enum/dialog-response';
import {MatExpansionModule} from '@angular/material/expansion';
import {GeneratedPeriod} from '../../model/generated-period';
import {ScriptUtil} from '../../util/script-util';
import {DateTime} from 'luxon';
import {MatRadioButton, MatRadioGroup} from "@angular/material/radio";
import {MatDivider} from "@angular/material/divider";
import {SpecialType} from "../../model/period";
import {EasterDay} from "../../enum/easter-day";
import {ChristmasDay} from "../../enum/christmas-day";

@Component({
  selector: 'app-event-edit-dialog',
  providers: [
    provideNativeDateAdapter()
  ],
  imports: [
    CommonModule,
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
    MatTooltip,
    AsyncPipe,
    ReactiveFormsModule,
    MatSelectModule,
    TranslatePipe,
    TitleCasePipe,
    MatExpansionModule,
    MatRadioGroup,
    MatRadioButton,
    MatDivider,
  ],
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl: './add-full-event-dialog.component.html',
  styleUrls: ['../../../styles.scss','./add-full-event-dialog.component.css']
})
export class AddFullEventDialogComponent {
  readonly dialogRef = inject(MatDialogRef<AddFullEventDialogComponent>);
  readonly data = inject<DialogData>(MAT_DIALOG_DATA);

  periodCtr = new FormControl<GeneratedPeriod | null>(this.data.event.period);
  filteredPeriods$: Observable<GeneratedPeriod[]> = of([]);

  public singleEvent: boolean = this.data.event.renum === Renum.NONE;
  public specialPeriodType?: SpecialType | null = null;

  public selectableGenPeriods: GeneratedPeriod[] = [];
  public titles: string[] = MassUtil.getTitles(this.data.event.rite);

  public readonly allDays = Object.values(Day);
  public readonly easterDays = Object.values(EasterDay);
  public readonly christmasDays = Object.values(ChristmasDay);
  public readonly recurrences = recurrences;
  public readonly rites = Object.values(Rite);
  public readonly languages = Object.values(LanguageCode);
  public readonly Object = Object;

  public dayError: boolean = false;
  public christmasDayError: boolean = false;
  public easterDayError: boolean = false;

  selectedDays: Day | Day[] = this.data.event.selectedDays;
  selectedChristmasDay?: ChristmasDay | null = this.data.event.selectedChristmasDay;
  selectedEasterDay?: EasterDay | null = this.data.event.selectedEasterDay;

  public selectedPeriodIsMultiday: boolean | null = null;

  constructor(
    readonly periodService: PeriodService,
    readonly translateService: TranslateService,
  ) {
    const hasPeriodId = !!(this.data.event.period && (this.data.event.period as any).periodId);

    // A választható időszakokat furcsa sorrendben jelenítjük meg direkt. 
    periodService.getSelectableGeneratedPeriodsByDate(this.data.event.start).subscribe(generatedPeriods => {
      this.selectableGenPeriods = generatedPeriods;
    });

    this.periodCtr.valueChanges.subscribe(value => {
      this.data.event.period = value;
      this.specialPeriodType = this.periodService.getPeriodById(value?.periodId)?.specialType;
      // Ensure titles are filtered when period changes
      this.applyTitleFilter();

      // Update multiday flag and warn if necessary
      this.maybeWarnIfNotMultiday(value);
    });
    this.filteredPeriods$ = this.periodCtr.valueChanges.pipe(
      startWith(''),
      map(value => {
        const filterValue = typeof value === 'string' ? value.toLowerCase() : value?.name.toLowerCase() ?? '';
        return this.selectableGenPeriods.filter(period => period.name.toLowerCase().includes(filterValue));
      })
    );

    if (this.data.event.period !== null) {
      this.specialPeriodType = this.periodService.getSpecialPeriodType(this.data.event.period.periodId);
      if (this.specialPeriodType !== null) {
        this.singleEvent = false;
      }

      // If the initial period is not multi-day, inform the user as well
      this.maybeWarnIfNotMultiday(this.data.event.period);
    }

    // Apply initial filtering of titles based on the current mode/period
    this.applyTitleFilter();
  }

  // Centralized check for multi-day flag and user notification
  private maybeWarnIfNotMultiday(selectedGeneratedPeriod: GeneratedPeriod | null | undefined): void {
    try {
      const period = selectedGeneratedPeriod ? this.periodService.getPeriodById(selectedGeneratedPeriod.periodId) : null;
      if (period) {
        // set the flag for template consumption
        this.selectedPeriodIsMultiday = !!period.multiDay;      
      } else {
        this.selectedPeriodIsMultiday = null;
      }
    } catch (e) {
      // ignore errors and clear flag
      this.selectedPeriodIsMultiday = null;
    }
  }

  onSave(): void {
    if (!this.singleEvent && ScriptUtil.isNull(this.data.event.period)) {
      this.periodCtr.setErrors({required: true});
      return;
    }

    if (!this.singleEvent && this.specialPeriodType === SpecialType.CHRISTMAS && ScriptUtil.isNull(this.selectedChristmasDay)) {
      this.christmasDayError = true;
      return;
    }

    if (!this.singleEvent && this.specialPeriodType === SpecialType.EASTER && ScriptUtil.isNull(this.selectedEasterDay)) {
      this.easterDayError = true;
      return;
    }

    const selectedGeneratedPeriod = this.data.event.period ?? this.periodCtr.value;
    const period = selectedGeneratedPeriod ? this.periodService.getPeriodById(selectedGeneratedPeriod.periodId) : null;
    if (!this.singleEvent && (period && period.multiDay === false)) {
      this.data.event.selectedChristmasDay = null;
      this.data.event.selectedEasterDay = null;
      this.data.event.selectedDays = [];
      this.data.event.renum = Renum.YEARLY;
      
    }

    else if (!this.singleEvent && this.specialPeriodType === null && (ScriptUtil.isNull(this.selectedDays) || this.selectedDays.length < 1)) {
      this.dayError = true;
      return;
    }

    if (this.specialPeriodType === SpecialType.CHRISTMAS) {
      this.data.event.selectedChristmasDay = this.selectedChristmasDay;
      this.data.event.selectedEasterDay = null;
      this.data.event.selectedDays = [];
    } else if (this.specialPeriodType === SpecialType.EASTER) {
      this.data.event.selectedChristmasDay = null;
      this.data.event.selectedEasterDay = this.selectedEasterDay;
      this.data.event.selectedDays = [];
    } else {
      this.data.event.selectedChristmasDay = null;
      this.data.event.selectedEasterDay = null;
      this.data.event.selectedDays = Array.isArray(this.selectedDays) ? this.selectedDays : [this.selectedDays];
    }
    this.dialogRef.close(DialogResponse.SAVE);
  }

  onNoClick(): void {
    this.dialogRef.close();
  }

  onRenumChange() {
    const multiDays = recurrences[this.data.event.renum].multiDays;

    const singleDay: Day | undefined = Array.isArray(this.selectedDays)
      ? this.selectedDays.length > 0
        ? this.selectedDays[0]
        : undefined
      : this.selectedDays;

    if (singleDay) {
      if (multiDays) {
        this.selectedDays = [singleDay];
      } else {
        this.selectedDays = singleDay;
      }
    } else {
      this.selectedDays = [];
    }
  }

  onRecurrenceModChange() {
    if (this.singleEvent) {
      this.data.event.renum = Renum.NONE;
    } else {
      this.data.event.renum = Renum.EVERY_WEEK;
    }
    // titles may need to be refreshed when recurrence mode changes
    this.applyTitleFilter();
  }

  onRiteChange() {
    this.titles = MassUtil.getTitles(this.data.event.rite);
    this.data.event.title = this.titles && this.titles.length > 0 ? this.translateService.instant(this.titles.at(0)!) : "";
    this.data.event.types = [];
    // titles may have to be filtered depending on the selected period / recurrence mode
    this.applyTitleFilter();
  }

  onStartTimeChange() {
    //ha módosítják az órát, a kizárás (ha volt) akkor is maradjon meg.
    const exdate = this.data.event.exdate;
    const startJs: Date = this.data.event.start;
    if (ScriptUtil.isNotNull(exdate)) {
      const startDt: DateTime = DateTime.fromObject(
        {
          year: startJs.getFullYear(),
          month: startJs.getMonth() + 1,
          day: startJs.getDate(),
          hour: startJs.getHours(),
          minute: startJs.getMinutes()
        },
      );
      const startTime: string = startDt.toFormat("HH:mm");
      this.data.event.exdate = exdate.map(dateStr => {
        const [datePart] = dateStr.split("T");
        return `${datePart}T${startTime}`;
      });
    }
  }

  onSelectedDaysChange() {
    this.dayError = false;
  }

  onSelectedChristmasDayChange() {
    this.christmasDayError = false;
  }

  onSelectedEasterDayChange() {
    this.easterDayError = false;

    // If Roman Catholic, pick a sensible default title key for the selected Easter-related day
    if (this.data.event.rite === Rite.ROMAN_CATHOLIC && this.selectedEasterDay) {
      let titleKey = '';
      switch (this.selectedEasterDay) {
        case EasterDay.TH:
          titleKey = 'MASS_TITLE.MASS_OF_THE_LORD_S_SUPPER';
          break;
        case EasterDay.FR:
          titleKey = 'MASS_TITLE.GOOD_FRIDAY_LITURGY';
          break;
        case EasterDay.SA:
          titleKey = 'MASS_TITLE.EASTER_VIGIL';
          break;
        case EasterDay.SU:
        case EasterDay.MO:
          titleKey = 'MASS_TITLE.HOLY_MASS';
          break;
      }
      if (titleKey) {
        this.data.event.title = this.translateService.instant(titleKey);        
      }
    }
  }

  resetPeriod(event: any) {
    event.preventDefault();
    event.stopPropagation();
    this.periodCtr.setValue(null);
  }

  displayPeriod(period: GeneratedPeriod | null): string {
    return period ? period.name : '';
  }

  // Filter out Easter-specific titles when in recurring mode and the selected period is NOT an Easter period
  private applyTitleFilter(): void {
    // Re-load the canonical list of titles for the current rite
    const originalTitles = MassUtil.getTitles(this.data.event.rite) || [];
    this.titles = [...originalTitles];

    if (this.titles.length === 0) return;

    const removals = [
      'MASS_TITLE.TRADITIONAL_MASS_OF_THE_LORD_S_SUPPER',
      'MASS_TITLE.TRADITIONAL_GOOD_FRIDAY_LITURGY',
      'MASS_TITLE.TRADITIONAL_EASTER_VIGIL',
      'MASS_TITLE.MASS_OF_THE_LORD_S_SUPPER',
      'MASS_TITLE.GOOD_FRIDAY_LITURGY',
      'MASS_TITLE.EASTER_VIGIL'
    ];

    // If not a single event (i.e. recurring) AND the selected period isn't Easter, remove Easter-specific titles
    const isEasterPeriod = this.specialPeriodType === SpecialType.EASTER;
    if (!this.singleEvent && !isEasterPeriod) {
      this.titles = this.titles.filter(t => !removals.includes(t));
      // If the currently selected title was removed, reset to first available
      if (this.data.event.title && removals.includes(this.data.event.title)) {
        this.data.event.title = this.titles && this.titles.length > 0 ? this.translateService.instant(this.titles[0]) : '';
      }
    }
  }

  protected readonly RiteMassTypes = RiteMassTypes;
  protected readonly Renum = Renum;
  protected readonly SpecialType = SpecialType;
}
