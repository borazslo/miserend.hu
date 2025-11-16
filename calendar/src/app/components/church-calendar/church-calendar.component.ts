import {
  AfterViewInit,
  Component,
  inject,
  Input,
  OnChanges,
  OnInit,
  output,
  SimpleChanges,
  ViewChild,
  TemplateRef,
  ViewContainerRef,
  EmbeddedViewRef
} from '@angular/core';
import {AsyncPipe, CommonModule} from '@angular/common';
import {FullCalendarComponent, FullCalendarModule} from '@fullcalendar/angular';
import {CalendarOptions, EventInput} from '@fullcalendar/core';
import {EventService} from '../../event.service';
import {MatDialog} from '@angular/material/dialog';
import {AddFullEventDialogComponent} from '../add-full-event-dialog/add-full-event-dialog.component';
import {PeriodService} from '../../services/period.service';
import {AddSimpleEventDialogComponent} from '../add-simple-event-dialog/add-simple-event-dialog.component';
import {MassUtil} from '../../util/mass-util';
import {Mass} from '../../model/mass';
import {CalendarEvent} from '../../model/calendar/calendar-event';
import {Church} from '../../model/church';
import {TranslatePipe, TranslateService} from '@ngx-translate/core';
import {MatButton} from '@angular/material/button';
import {DialogEvent} from '../../model/dialog-event';
import {DialogResponse} from '../../enum/dialog-response';
import {EventViewerDialogComponent} from '../event-viewer-dialog/event-viewer-dialog.component';
import {DateTimeUtil} from '../../util/date-time-util';
import {SuggestionPackage, SuggestionState} from '../../model/suggestion-package';
import {SuggestionUtil} from '../../util/suggestion-util';
import {ScriptUtil} from '../../util/script-util';
import {SpinnerService} from '../../services/spinner.service';
import {CalendarUtil} from '../../util/calendar-util';
import {MatSnackBarService} from '../../services/mat-snack-bar.service';
import {filter, Observable} from 'rxjs';
import {MatFormField, MatInput, MatLabel} from '@angular/material/input';
import {FormControl, FormsModule, ReactiveFormsModule} from '@angular/forms';
import {UserService} from '../../services/user.service';
import {PeriodExclusionDialogComponent} from '../period-exclusion-dialog/period-exclusion-dialog.component';
import {AddMessageDialogComponent} from '../add-message-dialog/add-message-dialog.component';
import {MatButtonToggle, MatButtonToggleGroup} from '@angular/material/button-toggle';
import {MatIcon} from '@angular/material/icon';
import {MatTooltip} from '@angular/material/tooltip';
import {SearchService} from '../../services/search.service';
import {GeneratedPeriod} from "../../model/generated-period";
import { eventListTemplate, EventListTemplateVars } from './event-list-template';

export interface SimpleDialogData {
  dateTime: Date;
  title: string;
}

export interface EventViewerDialogData {
  churchName: string;
  mass: Mass;
  suggestOrEditable: boolean;
  start: Date;
}

export interface DeleteDialogData {
  eventData: EventViewerDialogData;
  deleteOne: boolean;
}

export interface DialogData {
  title: string;
  event: DialogEvent;
}

@Component({
  selector: 'app-church-calendar',
  imports: [CommonModule, FullCalendarModule, AsyncPipe, MatButton, TranslatePipe, MatInput, MatFormField, MatLabel, FormsModule, ReactiveFormsModule, MatButtonToggle, MatButtonToggleGroup, MatIcon, MatTooltip],
  templateUrl: './church-calendar.component.html',
  styleUrls: ['../../../styles.scss', './church-calendar.component.css']
})
export class ChurchCalendarComponent implements OnInit, AfterViewInit, OnChanges {
  @Input() showHeader: boolean = true;
  @Input() showFooter: boolean = true;
  @Input() editable: boolean = false;
  @Input() suggestible: boolean = true;
  @Input({ required: true }) currentChurch!: Church;
  @Input({ required: true }) masses!: Map<number, Mass>;
  @Input() changes: Map<number, Mass> = new Map();
  @Input() deletedMasses: number[] = [];
  @Input() deletedDates: Map<number, string[]> = new Map();
  @Input() changedMasses: number[] = [];


  datesSet = output<string>();
  private edit = false;
  @ViewChild('calendar') calendarComponent!: FullCalendarComponent;
  // Template for rendering list-view event HTML via Angular bindings
  @ViewChild('eventListTemplate', { read: TemplateRef }) eventListTemplateRef!: TemplateRef<any>;
  @ViewChild('eventListTemplateContainer', { read: ViewContainerRef }) eventListTemplateContainer!: ViewContainerRef;

  private dialogEvent?: DialogEvent;
  readonly dialog = inject(MatDialog);
  calendarOptions?: CalendarOptions;
  eventsPromise?: Promise<EventInput[]>;

  private calEvents: CalendarEvent[] = [];

  selectedEvent?: any;
  selectedEventStart?: Date;
  selectedMassId?: number;
  selectedDate?: Date;
  suggestionSenderName: FormControl<string> = new FormControl();
  suggestionSenderEmail: FormControl<string> = new FormControl();
  suggestionSenderID: FormControl<number> = new FormControl();

  public calendarsTitle: string = '';

  // Show a simple mass list under the calendar in edit/admin contexts (editschedule)
  public showMassListInEdit: boolean = false;
  public massListGrouped: Array<{
    weight: number,
    periodName: string,
    masses: any[],
    startMonthDay?: string | null,
    endMonthDay?: string | null,
    startPeriodName?: string | null,
    endPeriodName?: string | null,
    color?: string | null
  }> = [];

  constructor(
    private readonly eventService: EventService,
    private readonly searchService: SearchService,
    private readonly periodService: PeriodService,
    private readonly snackBarService: MatSnackBarService,
    private readonly spinnerService: SpinnerService,
    private readonly userService: UserService,
    private readonly translateService: TranslateService,
  ) {}

  ngOnInit() {
    this.initializeCalendar();
    // determine whether we should render the mass list under the calendar
    const pathname: string = (typeof window !== 'undefined' && window.location && window.location.pathname) ? String(window.location.pathname) : '';
    this.showMassListInEdit = !!this.editable || pathname.indexOf('editschedule') !== -1;

    this.userService.loadUser().subscribe(user => {
      if (user) {
        this.suggestionSenderName.setValue(user.username);
        this.suggestionSenderName.updateValueAndValidity();
        this.suggestionSenderEmail.setValue(user.email);
        this.suggestionSenderEmail.updateValueAndValidity();
      }
    });
  }

  ngOnChanges(changes: SimpleChanges): void {
      this.reLoadCalendar();
  }


  ngAfterViewInit() {
    this.reLoadCalendar();
  }

  private loadEventsIntoCalendar(): Promise<CalendarEvent[]> {
    return new Promise(resolve => {
      this.periodService.generatedPeriods$
        .pipe(filter(periods => periods.length > 0))
        .subscribe(periods => {
          const events = MassUtil.createCalendarEvents(
            Array.from(this.masses.values()),
            periods,
            this.changedMasses,
            this.deletedMasses,
            this.deletedDates
          );
          resolve(events);
        });
    });
  }

  private initializeCalendar(): void {
    const timeZone: string = this.currentChurch.timeZone;
    // use the options without FullCalendar's built-in toolbar so the custom Angular/Material header is the only visible header
    this.calendarOptions = CalendarUtil.getSimpleCalendarOptionsWithoutHeader(timeZone);

    // If we're in an editable/admin context or the URL indicates editschedule, prefer the week view as the default
    const preferWeekView = this.editable || (typeof window !== 'undefined' && window.location && window.location.pathname && window.location.pathname.indexOf('editschedule') !== -1);
    if (preferWeekView) {
      this.calendarOptions.initialView = 'timeGridWeek';
    }

    this.calendarOptions = {
      ...this.calendarOptions,
      eventClick: (arg: any) => this.handleEventClick(arg),
      datesSet: (arg: any) => this.onDatesSet(arg),
      // Render custom event content so we can append a language flag ant types in list views
      eventContent: (info: any) => this.renderEventContent(info),
      ...((this.editable || this.suggestible) && {dateClick: (arg: any) => this.handleDateClick(arg)} ),
      eventDidMount:  function (info) {
        const eventDate = info.event.startStr.slice(0, 10);
        const recentExDates:string[] = info.event.extendedProps['recentExDates'];
        if (recentExDates?.includes(eventDate)) {
            info.el.style.backgroundColor = '#ff4d4d';
            info.el.style.borderColor = '#ff4d4d';
        }
      }
    };
  }

  private handleEventClick(arg: any) {
    this.selectedDate = undefined;
    this.selectedMassId = arg.event.extendedProps.massId;
    this.selectedEvent = arg.event;
    this.selectedEventStart = new Date(arg.event.startStr);
    this.openEventViewerDialog();
  }

  handleEventMount(info: any) {
    const massId = info.event.extendedProps['massId'];
    if (ScriptUtil.isNull(massId) || massId < 0) {
      info.el.style.border = '2px dashed #ff9800';
    }
    info.el.setAttribute('title', info.event.title);
  }

  openEventViewerDialog() {
    if (this.selectedMassId === undefined) {
      return;
    }

    //először megnézzük, hogy az újak/változottak közt ott van-e már
    let mass: Mass | undefined = undefined;
    if (this.changes.has(this.selectedMassId)) {
      mass = this.changes.get(this.selectedMassId);
    }

    //ha nincs ott, akkor megnézzük a többin
    if (!mass && this.masses.has(this.selectedMassId)) {
      mass = this.masses.get(this.selectedMassId);
    }

    if (!mass) {
      console.error('NINCS ILYEN MISE ID: ' + this.selectedMassId);
      alert('NINCS ILYEN MISE ID: ' + this.selectedMassId);
      return;
    }

    const dialogRef = this.dialog.open(EventViewerDialogComponent, {
      data: {churchName: this.currentChurch.name, mass: mass, suggestOrEditable: this.editable || this.suggestible, start: this.selectedEventStart}
    });

    dialogRef.afterClosed().subscribe(result => {
      this.processEventViewerDialogResult(result);
    });
  }

  // Open the same event viewer popup when a mass row title is clicked in the editable mass list
  public openMassFromList(m: any): void {
    if (!m || !m.id) return;
    this.selectedMassId = m.id;
    this.selectedEventStart = m.startDate ? new Date(m.startDate) : undefined;

    // Ensure selectedMassId is defined for TS-safe map access
    if (this.selectedMassId === undefined || this.selectedMassId === null) {
      return;
    }
    const id: number = this.selectedMassId as number;

    // Instead of opening the EventViewer, open the full editor for the existing liturgy
    let mass: Mass | undefined = undefined;
    if (this.changes.has(id)) {
      mass = this.changes.get(id);
    } else if (this.masses.has(id)) {
      // clone to avoid mutating original until saved
      mass = ScriptUtil.clone(this.masses.get(id)!);
    }

    if (!mass) {
      console.error('NINCS ILYEN MISE ID: ' + id);
      return;
    }

    // Prepare dialog event for editing the existing liturgy (full editor)
    this.dialogEvent = MassUtil.massToDialogEvent(mass);
    if (mass.periodId) {
      this.dialogEvent.period =
        this.periodService.getCurrentGeneratedPeriodByPeriodId(mass.periodId, new Date(mass.startDate));
      this.setSpecialPeriodDays(mass);
    }
    this.openFullDialog('EDIT_MASS');
  }

  private processEventViewerDialogResult(result: any) {
    if (this.selectedMassId === undefined) {
      return;
    }

    if (result === DialogResponse.DELETE_ONE) {
      if (!this.changes.has(this.selectedMassId)) {
        const mass = this.masses.get(this.selectedMassId);
        if (mass) {
          this.changes.set(this.selectedMassId, ScriptUtil.clone(mass));
        }
      }
      if (this.changes.has(this.selectedMassId)) {
        const mass = this.changes.get(this.selectedMassId);
        if (mass) {
          if (this.selectedEventStart) {
            const currentStartStr = DateTimeUtil.getIsoString(this.selectedEventStart);
            if (mass.exdate) {
              mass.exdate.push(currentStartStr);
            } else {
              mass.exdate = [currentStartStr];
            }

            this.calEvents = this.calEvents.filter(event => event.extendedProps.massId !== this.selectedMassId);

            this.calEvents.push(
              ...MassUtil.createCalendarEvent(mass, this.periodService.generatedPeriods$.getValue(), this.deletedDates.get(mass.id))
            );

            this.calendarComponent.getApi().removeAllEvents();
            this.calendarComponent.getApi().addEventSource(this.calEvents);
          }
        }
      }
    } else if(result === DialogResponse.DELETE_ALL) {
      if (this.selectedMassId >= 0) {
        this.deletedMasses.push(this.selectedMassId);
      }
      if (this.changes.has(this.selectedMassId)) {
        this.changes.delete(this.selectedMassId);
      }

      this.calEvents = this.calEvents.filter(event => event.extendedProps.massId !== this.selectedMassId);
      this.calendarComponent.getApi().removeAllEvents();
      this.calendarComponent.getApi().addEventSource(this.calEvents);

    } else if(result === DialogResponse.EVENT_VIEWER_EDIT_ALL || result === DialogResponse.EVENT_VIEWER_EDIT_ONE) {
      const editOne: boolean = result === DialogResponse.EVENT_VIEWER_EDIT_ONE;

      let mass: Mass | undefined;
      if (this.changes.has(this.selectedMassId)) {
        mass = this.changes.get(this.selectedMassId);
      } else if (this.masses.has(this.selectedMassId)) {
        mass = this.masses.get(this.selectedMassId);
      }

      if (mass) {
        this.dialogEvent = editOne ? MassUtil.massToDialogEventEditOne(mass, this.selectedEventStart!) : MassUtil.massToDialogEvent(mass);
        if (!editOne && mass.periodId) {
          this.dialogEvent.period =
            this.periodService.getCurrentGeneratedPeriodByPeriodId(mass.periodId, new Date(mass.startDate));
          this.setSpecialPeriodDays(mass);
        }
        this.openFullDialog('EDIT_MASS');
      }
    }
  }

  private handleDateClick(arg: any) {
    const viewType = this.calendarComponent.getApi().view.type;
    this.selectedMassId = undefined;
    this.selectedEvent = undefined;
    this.selectedEventStart = undefined;
    this.selectedDate = new Date(arg.dateStr);
    if (viewType === 'dayGridDay' || viewType === 'timeGridWeek') {
      this.openEditDialog();
    } else {
      this.dialogEvent = CalendarUtil.generateDialogEvent(this.currentChurch, this.translateService, this.selectedDate);
      this.openFullDialog('ADD_NEW_MASS', this.selectedDate);
    }
  }

  openEditDialog() {
    if (this.selectedDate === undefined) {
      return;
    }

    if(!this.edit) {
      const messageDialogRef = this.dialog.open(AddMessageDialogComponent, {
        data: {message: "Szerkeszteni szeretnéd a naptárat?", decision: true}
      });

      messageDialogRef.afterClosed().subscribe(result => {
        if (result === DialogResponse.CONTINUE) {
          this.edit = true;
          this.openSimpleDialog();
        }
      });
    } else{
      this.openSimpleDialog();
    }
  }

  /**
   * Megnyit egy egyszerű, csak olvasható felugró ablakot, amiben a paraméterül kapott dátumnak megfelelően kiírásra
   * kerül az újonnan felvenni kívánt mise időpontja
   * Ezt vagy el lehet menteni, vagy a továbbnavigálni egy részletes beállítási felületre
   */
  openSimpleDialog() {
    if (this.selectedDate === undefined) {
      return;
    }

    const dialogRef = this.dialog.open(AddSimpleEventDialogComponent, {
      data: {dateTime: this.selectedDate, title: MassUtil.getSimpleTitle4Church(this.currentChurch!)}
    });

    dialogRef.afterClosed().subscribe(result => {
      if (result === DialogResponse.SAVE_SIMPLE) {
        this.saveSimpleEvent();
      } else if(result === DialogResponse.MORE_DETAILS) {
        this.dialogEvent = CalendarUtil.generateDialogEvent(this.currentChurch, this.translateService, this.selectedDate);
        this.openFullDialog('ADD_NEW_MASS', this.selectedDate);
      }
    });
  }

  private saveSimpleEvent() {
    if (this.selectedDate === undefined) {
      return;
    }

    const newMassId = MassUtil.generateTmpMassId();
    const simpleCalendarEvent: CalendarEvent =
      MassUtil.createSimpleCalendarEventByDate(this.selectedDate, this.currentChurch!.rite, newMassId, this.translateService);
    const simpleMass: Mass = MassUtil.createSimpleMassByDate(this.selectedDate, this.currentChurch!, newMassId, this.translateService);

    this.changes.set(simpleMass.id!, simpleMass);

    this.calEvents.push(simpleCalendarEvent);
    this.calendarComponent.getApi().removeAllEvents();
    this.calendarComponent.getApi().addEventSource(this.calEvents);
  }

  openFullDialog(title: string, date?: Date) {
    if (date) {
      this.dialogEvent!.start = date;
    }

    const dialogRef = this.dialog.open(AddFullEventDialogComponent, {
      data: {title: title, event: this.dialogEvent}
    });

    dialogRef.afterClosed().subscribe(result => {

      if (ScriptUtil.isNull(this.dialogEvent)) {
        return;
      }

      if (result === 'SAVE') {
        if (this.dialogEvent.editOne) {
          //EBBEN AZ ESETBEN LÉTREHOZUNK EGY TELJESEN ÚJ MISÉT, AMI NEM TARTOZIK A SZÜLŐHÖZ
          const parentMassId: number | undefined = this.selectedMassId;
          const newMassId: number = MassUtil.generateTmpMassId();
          const newSingleCalendarEvent: CalendarEvent = MassUtil.createEventByType(this.dialogEvent, newMassId);
          const newSingleMass: Mass = MassUtil.createMass(newSingleCalendarEvent, this.dialogEvent, this.currentChurch!, newMassId);

          if (parentMassId) {
            let parentMass: Mass | undefined;
            if (this.changes.has(parentMassId)) {
              parentMass = this.changes.get(parentMassId);
            } else if(this.masses.has(parentMassId)) {
              parentMass = ScriptUtil.clone(this.masses.get(parentMassId)!);
            }

            if (parentMass) {
              const startStr = DateTimeUtil.getIsoString(this.selectedEventStart!);
              if (parentMass.exdate) {
                parentMass.exdate.push(startStr);
              } else {
                parentMass.exdate = [startStr];
              }

              this.calEvents = this.calEvents.filter(event => event.extendedProps.massId !== parentMassId);
              this.calEvents.push(
                ...MassUtil.createCalendarEvent(parentMass, this.periodService.generatedPeriods$.getValue())
              );

              this.changes.set(parentMassId, parentMass);
            }
          }

          this.changes.set(newSingleMass.id!, ScriptUtil.clone(newSingleMass));
          this.calEvents.push(newSingleCalendarEvent);

          this.calendarComponent.getApi().removeAllEvents();
          this.calendarComponent.getApi().addEventSource(this.calEvents);

        } else {
          const newMassId: number = this.selectedMassId ? this.selectedMassId : MassUtil.generateTmpMassId();
          const periodId = this.dialogEvent.period?.periodId;
          const periodWeight = this.dialogEvent.period?.weight;
          const specialPeriodType = this.periodService.getSpecialPeriodType(periodId);
          const calendarEvent: CalendarEvent = MassUtil.createEventByType(this.dialogEvent, newMassId, specialPeriodType);
          const mass: Mass = MassUtil.createMass(calendarEvent, this.dialogEvent, this.currentChurch!, newMassId);

          this.calEvents = this.calEvents.filter(event => event.extendedProps.massId !== newMassId);

          const recentlyExclusionSourcePeriodIds: number[] = this.excludeNewMassFromLowerPeriodMasses(periodId, periodWeight);

          if (periodId) {
            const generatedPeriods = this.periodService.getGeneratedPeriodsByPeriodId(periodId);
            const calendarEvents: CalendarEvent[] = MassUtil.createEventByPeriods(calendarEvent, generatedPeriods);
            this.calEvents.push(...calendarEvents);
          } else {
            this.calEvents.push(calendarEvent);
          }

          const recentlyExcludedPeriodIds = this.excludeHigherPeriodMassesFromNewMass(mass, periodId, periodWeight);

          this.showExclusionDialogIfNeed(periodId!, recentlyExclusionSourcePeriodIds, recentlyExcludedPeriodIds);

          this.changes.set(mass.id!, ScriptUtil.clone(mass));

          this.calendarComponent.getApi().removeAllEvents();
          this.calendarComponent.getApi().addEventSource(this.calEvents);
        }
      }
    });
  }

  public onSaveCalendar() {
    this.spinnerService.show();
    const changesArray = Array.from(this.changes.values());
    this.eventService.saveChanges(this.currentChurch!.id, changesArray, this.deletedMasses).subscribe(masses => {
      this.changes.clear();
      this.deletedMasses = [];
      this.masses = new Map(masses.map(e => [e.id!, e]));
      this.reLoadCalendar();
      this.snackBarService.success('Sikeres mentés!');

      //TODO: EZT MAJD HÁTTÉRBEN
      const currentYear = new Date().getFullYear();
      const years: number[] = [currentYear - 1, currentYear, currentYear + 1];
      this.searchService.generateMasses(years, this.currentChurch!.id).subscribe();
    });
  }

  public onSendToApprove() {
    this.spinnerService.show();

    const suggestionPackage: SuggestionPackage = {
      churchId: this.currentChurch!.id,
      senderName: this.suggestionSenderName.value,
      senderEmail: this.suggestionSenderEmail.value,
      senderUserId: this.suggestionSenderID.value,
      suggestions: SuggestionUtil.generateSuggestions(this.masses, this.changes, this.deletedMasses),
      state: SuggestionState.PENDING,
      createdAt: new Date()
    }

    this.eventService.sendToApprove(this.currentChurch!.id, suggestionPackage).subscribe(res => {
      this.changes.clear();
      this.deletedMasses = [];
      this.deletedDates.clear();
      this.reLoadCalendar();
      this.dialog.open(AddMessageDialogComponent, {
        data: {message: "Javaslatod sikeresen beküldve! Amint jóváhagyják, megjelenik a naptárban.", decision: false}
      });
    });
  }

  public onAcceptSuggestion(selectedSuggestionPackage: SuggestionPackage, origMasses: Map<number, Mass>): Observable<{
    suggestionPackages: SuggestionPackage[];
    calendarMasses: Mass[]
  }> {

    selectedSuggestionPackage.suggestions.forEach(suggestion => {
      if (suggestion.changes && suggestion.changes.id && suggestion.changes.id < 0) {
        delete suggestion.changes.id;
      }
    });

      return this.eventService.simpleAcceptSuggestionPackage(selectedSuggestionPackage);
  }

  onRejectSuggestion(selectedSuggestionPackage: SuggestionPackage) : Observable<{
    suggestionPackages: SuggestionPackage[];
    calendarMasses: Mass[]
  }>  {
    return this.eventService.simpleRejectSuggestionPackage(selectedSuggestionPackage);
  }

  public reLoadCalendar() {
    if (this.calendarComponent) {
      this.loadEventsIntoCalendar().then(events => {
        this.calEvents = events;
        this.calendarComponent.getApi().removeAllEvents();
        this.calendarComponent.getApi().removeAllEventSources();
        this.calendarComponent.getApi().addEventSource(events);
        this.spinnerService.hide();

        // rebuild the editable mass list when in edit/admin context
        if (this.showMassListInEdit) {
          this.buildMassList();
        }
      });
    }
  }

  onResetCalendar() {
    this.spinnerService.show();
    this.changes.clear();
    this.deletedMasses = [];
    this.reLoadCalendar();
  }

  public next() {
    this.calendarComponent.getApi().next();
  }

  public prev() {
    this.calendarComponent.getApi().prev();
  }

  // Accept listWeek as well so template buttons can call changeView('listWeek') without type errors
  public changeView(view: 'dayGridDay' | 'dayGridMonth' | 'timeGridWeek' | 'listWeek') {
    this.calendarComponent.getApi().changeView(view as any);
  }

  private onDatesSet(arg : any) {
    const title: string = arg.view.title;
    this.datesSet.emit(title);
    this.setCalendarsTitle(title);
  }

  /**
   * Ütközésvizsgálat - csak ha ismétlődő esemény - csak a kisebb súlyúakból zárunk ki
   * új mise felvételénél, nézzük meg, hogy van-e periódusa
   * ha van, akkor az összes kisebb periódussúlyú miséhez adjuk hozzá ezt, mint egy eleme az experiodnak
   */
  private excludeNewMassFromLowerPeriodMasses(periodId?: number, periodWeight?: number): number[] {
    const recentlyExclusionSourcePeriodIds: number[] = [];

    if (ScriptUtil.isNotNull(periodId) && ScriptUtil.isNotNull(periodWeight) && periodWeight > 1) {
      const lowerPeriodWeightMassIds: number[] = [];
      for (const m of this.masses.values()) {
        if (ScriptUtil.isNotNull(m.periodId)) {
          const mPeriod = this.periodService.getPeriodById(m.periodId);
          if (ScriptUtil.isNotNull(mPeriod) && mPeriod.weight < periodWeight) {
            lowerPeriodWeightMassIds.push(m.id);
          }
        }
      }

      for (const m of this.changes.values()) {
        if (ScriptUtil.isNotNull(m.periodId)) {
          const mPeriod = this.periodService.getPeriodById(m.periodId);
          if (ScriptUtil.isNotNull(mPeriod) && mPeriod.weight < periodWeight) {
            if (!lowerPeriodWeightMassIds.includes(m.id)) {
              lowerPeriodWeightMassIds.push(m.id);
            }
          }
        }
      }

      let globalChanged: boolean = false;

      for (let mId of lowerPeriodWeightMassIds) {
        let m = this.changes.get(mId);
        if (ScriptUtil.isNull(m) && this.masses.has(mId)) {
          m = ScriptUtil.clone(this.masses.get(mId));
        }
        if (ScriptUtil.isNull(m)) {
          console.error(`Hiányzó mise: ${mId}`);
          continue;
        }

        let changed: boolean = false;
        if (ScriptUtil.isNotNull(m.periodId) && m.periodId === periodId) {
          changed = false;          
        } else if (ScriptUtil.isNotNull(m.experiod)) {
          if (!m.experiod.includes(periodId)) {
            m.experiod.push(periodId);
            changed = true;
          }
        } else {
          m.experiod = [periodId];
          changed = true;
        }
        if (changed) {
          this.changes.set(mId, m);
          globalChanged = true;

          //ha még nem volt hasonló üzenet, hogy a most hozzáadott mise periódusát kizárjuk ebből az időszakból, akkor majd most megtesszük
          if (ScriptUtil.isNotNull(m.periodId) && !recentlyExclusionSourcePeriodIds.includes(m.periodId) &&
              this.hasPreviouslySentNotification(m.periodId, periodId)) {
            recentlyExclusionSourcePeriodIds.push(m.periodId);
          }
        }
      }

      if (globalChanged) {
        this.calEvents = this.calEvents
          .filter(event => !lowerPeriodWeightMassIds.includes(event.extendedProps.massId));
        lowerPeriodWeightMassIds.forEach(lowerPeriodWeightMassId => {
          const lowerPeriodWeightMass = this.changes.get(lowerPeriodWeightMassId);
          if (ScriptUtil.isNotNull(lowerPeriodWeightMass)) {
            this.calEvents.push(
              ...MassUtil.createCalendarEvent(
                lowerPeriodWeightMass,
                this.periodService.generatedPeriods$.getValue(),
              )
            );
          }
        });
      }
    }
    return recentlyExclusionSourcePeriodIds;
  }

  /**
   * Itt végignézzük, hogy milyen ennél nagyobb periódussúlyú misék vannak, és azokat kizárjuk ebből
   * Ha pl. volt már nyár felvéve, akkor ha most egész éveset hozok létre, akkor a nyarat kizárjuk ebből
   */
  private excludeHigherPeriodMassesFromNewMass(mass: Mass, periodId?: number, periodWeight?: number): number[] {
    const recentlyExcludedPeriodIds: number[] = [];

    if (ScriptUtil.isNotNull(periodId) && ScriptUtil.isNotNull(periodWeight)) {
      const higherPeriodIds: number[] = [];
      for (const m of this.masses.values()) {
        if (ScriptUtil.isNotNull(m.periodId)) {
          const mPeriod = this.periodService.getPeriodById(m.periodId);
          if (ScriptUtil.isNotNull(mPeriod) && mPeriod.weight > periodWeight && !higherPeriodIds.includes(m.periodId)) {
            higherPeriodIds.push(m.periodId);
          }
        }
      }

      for (const m of this.changes.values()) {
        if (ScriptUtil.isNotNull(m.periodId)) {
          const mPeriod = this.periodService.getPeriodById(m.periodId);
          if (ScriptUtil.isNotNull(mPeriod) && mPeriod.weight > periodWeight) {
            if (!higherPeriodIds.includes(m.periodId)) {
              higherPeriodIds.push(m.periodId);
            }
          }
        }
      }

      let globalChanged: boolean = false;

      if (higherPeriodIds.length > 0 && ScriptUtil.isNull(mass.experiod)) {
        mass.experiod = [];
      }
      higherPeriodIds.forEach(higherPeriodId => {
        if (!mass.experiod!.includes(higherPeriodId)) {
          mass.experiod!.push(higherPeriodId);
          globalChanged = true;

          //ha még nem volt hasonló üzenet, hogy most hozzáadott mise periódusából kizárjuk ezt az időszakot, akkor majd most megtesszük
          if (!recentlyExcludedPeriodIds.includes(higherPeriodId) && !this.hasPreviouslySentNotification(periodId, higherPeriodId)) {
            recentlyExcludedPeriodIds.push(higherPeriodId);
          }
        }
      });

      if (globalChanged) {
        this.changes.set(mass.id, mass);

        this.calEvents = this.calEvents.filter(event => event.extendedProps.massId !== mass.id);
        this.calEvents.push(
          ...MassUtil.createCalendarEvent(
            mass,
            this.periodService.generatedPeriods$.getValue(),
          )
        );
      }
    }
    return recentlyExcludedPeriodIds;
  }

  private hasPreviouslySentNotification(periodId: number, excludedPeriodId: number): boolean {
    for (const mass of this.masses.values()) {
      if (mass.periodId === periodId && ScriptUtil.isNotNull(mass.experiod) && mass.experiod.includes(excludedPeriodId) ) {
       return true;
      }
    }
    for (const mass of this.changes.values()) {
      if (mass.periodId === periodId && ScriptUtil.isNotNull(mass.experiod) && mass.experiod.includes(excludedPeriodId) ) {
        return true;
      }
    }
    return false;
  }

  private showExclusionDialogIfNeed(periodId: number, recentlyExclusionSourcePeriodIds: number[], recentlyExcludedPeriodIds: number[]) {
    if (ScriptUtil.isNotNull(periodId) &&
      (recentlyExclusionSourcePeriodIds.length > 0 || recentlyExcludedPeriodIds.length > 0)) {

      const recentlyExclusionSourcePeriods = this.periodService.getGeneratedPeriodsByPeriodIds(recentlyExclusionSourcePeriodIds);
      const recentlyExcludedPeriods = this.periodService.getGeneratedPeriodsByPeriodIds(recentlyExcludedPeriodIds);
      const generatedPeriods = this.periodService.getGeneratedPeriodsByPeriodId(periodId);

      const filteredRecentlyExclusionSourcePeriodIds = this.filterOverlappingPeriodIds(
          generatedPeriods,
          recentlyExclusionSourcePeriods,
          recentlyExclusionSourcePeriodIds
      );

      const filteredRecentlyExcludedPeriodIds = this.filterOverlappingPeriodIds(
          generatedPeriods,
          recentlyExcludedPeriods,
          recentlyExcludedPeriodIds
      );

      if (filteredRecentlyExclusionSourcePeriodIds.length > 0 || filteredRecentlyExcludedPeriodIds.length > 0) {
        this.dialog.open(PeriodExclusionDialogComponent, {
          data: {
            periodName: this.periodService.getPeriodNameById(periodId),
            recentlyExcludedPeriodNames: this.periodService.getPeriodNamesByIds(filteredRecentlyExcludedPeriodIds),
            recentlyExclusionSourcePeriodNames: this.periodService.getPeriodNamesByIds(filteredRecentlyExclusionSourcePeriodIds)
          }
        });
      }
    }
  }

  get hasUnsavedChanges(): boolean {
    return this.changes.size > 0 || this.deletedMasses.length > 0;
  }

  public setCalendarsTitle(title: string) {
    setTimeout(() => {
      this.calendarsTitle = title;
    });
  }

  private setSpecialPeriodDays(mass: Mass) {
    if (this.periodService.isChristmasPeriod(mass.periodId!)) {
      this.dialogEvent!.selectedChristmasDay = MassUtil.getChristmasDayByMass(mass);
    }
    if (this.periodService.isEasterPeriod(mass.periodId!)) {
      this.dialogEvent!.selectedEasterDay = MassUtil.getEasterDayByMass(mass);
    }
  }

  private periodsOverlap(a: GeneratedPeriod, b: GeneratedPeriod): boolean {
    const aStart = new Date(a.startDate);
    const aEnd = new Date(a.endDate);
    const bStart = new Date(b.startDate);
    const bEnd = new Date(b.endDate);
    return aStart < bEnd && aEnd > bStart;
  }

  private filterOverlappingPeriodIds(
      currentPeriods: GeneratedPeriod[],
      targetPeriods: GeneratedPeriod[],
      targetIds: number[]
  ): number[] {
    return targetIds.filter(id => {
      const targetGroup = targetPeriods.filter(p => p.periodId === id);
      return targetGroup.some(t =>
          currentPeriods.some(c => this.periodsOverlap(c, t))
      );
    });
  }

  // Create event HTML that includes time, title, a flag, and other icons (if available)
  renderEventContent(info: any) {
    try {
      // determine current view type (use info.view when available)
      const viewType = info.view?.type || (this.calendarComponent ? this.calendarComponent.getApi().view.type : '');
      const isListView = typeof viewType === 'string' && viewType.startsWith('list');

      // For list views build a list-style row (with optional flag)
      if (isListView) {
        // Render the Angular template into DOM nodes and serialize to HTML so translation pipes
        // and other Angular bindings work.
        const massId = info.event.extendedProps?.massId;
        let lang = null as string | null;
        let types: string[] = [];
        let mass: any | undefined = undefined;
        if (massId != null) {
          if (this.changes && this.changes.has(massId)) {
            mass = this.changes.get(massId);
          } else if (this.masses && this.masses.has(massId)) {
            mass = this.masses.get(massId);
          }
          if (mass && mass.lang) lang = mass.lang;
          if (mass && mass.types) types = mass.types;
        }

        // Create embedded view from ng-template, pass context values and serialize
        if (this.eventListTemplateRef && this.eventListTemplateContainer) {
          const ctx = { timeText: info.timeText || '', title: info.event.title || '', lang: lang, types: types };
          const view: EmbeddedViewRef<any> = this.eventListTemplateContainer.createEmbeddedView(this.eventListTemplateRef, ctx);
          view.detectChanges();
          // serialize root nodes
          const html = view.rootNodes.map((n: any) => n.nodeType === 1 ? (n as HTMLElement).outerHTML : n.textContent || '').join('');
          view.destroy();
          return { html };
        }
      }

      // For non-list views return simple markup using FullCalendar's standard classes so
      // default styles (colors, layout) are preserved.
      const timeHtml = info.timeText ? `<span class="fc-event-time">${info.timeText}</span>` : '';
      const titleHtml = `<span class="fc-event-title">${info.event.title}</span>`;
      return { html: `${timeHtml} ${titleHtml}` };
    } catch (e) {
      return { html: info.event.title };
    }
  }

// RRule parsing helpers for human-readable recurrence description
// A masslist használja
  private getDaysFromRRule(mass: Mass): string {
    const days = mass.rrule?.byweekday;
    if (ScriptUtil.isNotNull(days)) {
      const translatedDays: string[] = [...days].map(d => this.translateService.instant('DAYS.ON.' + d));
      return translatedDays.join(', ');
    }
    return '';
  }

  private getWeekFromRRule(mass: Mass): string | null {
    const rrule = mass.rrule;
    if (ScriptUtil.isNull(rrule) || rrule.freq !== 'weekly') {
      return null;
    }

    if (rrule.byweekno && rrule.byweekno.length > 0) {
      const isEven = rrule.byweekno.every((n: number) => n % 2 === 0);
      const isOdd = rrule.byweekno.every((n: number) => n % 2 === 1);
      const week: string = this.translateService.instant(isEven ? 'RRULE.ON.EVEN' : isOdd ? 'RRULE.ON.ODD' : '');
      return week || null;
    }

    return this.translateService.instant('RRULE.ON.EVERY_WEEK');
  }

  private getMonthFromRRule(mass: Mass): string | null {
    const rrule = mass.rrule;
    if (ScriptUtil.isNotNull(rrule) && ScriptUtil.isNotNull(rrule.bysetpos)) {
      const renumByPos = MassUtil.renumByPos(rrule.bysetpos);
      if (renumByPos != null) {
        return this.translateService.instant('RRULE.ON.' + renumByPos);
      }
    }
    return null;
  }

  private getEasterFromMass(mass: Mass): string | null {
    if (ScriptUtil.isNotNull(mass.periodId)) {
      const specialPeriodType = this.periodService.getSpecialPeriodType(mass.periodId);
      // SpecialType enum isn't imported here; periodService method returns something comparable to suggestions logic
      if (specialPeriodType === (window as any).SpecialType?.EASTER || specialPeriodType === 'EASTER') {
        const rrule = mass.rrule;
        if (ScriptUtil.isNotNull(rrule) && ScriptUtil.isNotNull(rrule.byweekday) && rrule.byweekday.length === 1) {
          let easterDay = rrule.byweekday[0];
          if (easterDay != null) {
            return this.translateService.instant("EASTER_DAYS." + easterDay);
          }
        }
      }
    }
    return null;
  }

  private getChristmasFromRRule(mass: Mass): string | null {
    const rrule = mass.rrule;
    if (ScriptUtil.isNotNull(rrule) && rrule.bymonth === 12 && ScriptUtil.isNotNull(rrule.bymonthday)) {
      let christmasDay = MassUtil.christmasDayByMonthday(rrule.bymonthday);
      if (christmasDay != null) {
        return this.translateService.instant("CHRISTMAS_DAYS." + christmasDay);
      }
    }
    return null;
  }

  private getReadableRRule(mass: Mass): string {
    if (!mass || ScriptUtil.isNull(mass.rrule)) return '';
    const parts: string[] = [];
    const days = this.getDaysFromRRule(mass);
    if (days) parts.push(days);
    const week = this.getWeekFromRRule(mass);
    if (week) parts.push(week);
    const month = this.getMonthFromRRule(mass);
    if (month) parts.push(month);
    const easter = this.getEasterFromMass(mass);
    if (easter) parts.push(easter);
    const christmas = this.getChristmasFromRRule(mass);
    if (christmas) parts.push(christmas);
    return parts.join(', ');
  }
  // Itt ért véget a masslist használta rész

  private buildMassList(): void {
    // Merge base masses and changes (changes override base), exclude deleted masses.
    const combined = new Map<number, Mass>();
    for (const m of this.masses.values()) {
      combined.set(m.id!, m);
    }
    for (const [id, changed] of this.changes.entries()) {
      combined.set(id, changed);
    }
    for (const del of this.deletedMasses) {
      if (combined.has(del)) combined.delete(del);
    }

    // groups now carry additional optional metadata for header rendering
    const groups: {[key: number]: {weight: number, periodName: string, masses: any[], startMonthDay?: string | null, endMonthDay?: string | null, startPeriodName?: string | null, endPeriodName?: string | null, color?: string | null}} = {};

    combined.forEach(m => {
      const period = m.periodId ? this.periodService.getPeriodById(m.periodId) : null;
      const weight = period && period.weight ? period.weight : 0;
      const pname = period && period.name ? period.name : '';

      if (!groups[weight]) {
        // try to fetch a representative generated period to obtain a color
        let color: string | null = null;
        if (period && period.id) {
          const gen = this.periodService.getGeneratedPeriodsByPeriodId(period.id);
          if (Array.isArray(gen) && gen.length > 0) {
            color = gen[0].color || null;
          }
        }

        groups[weight] = {
          weight: weight,
          periodName: pname,
          masses: [],
          startMonthDay: period ? period.startMonthDay : null,
          endMonthDay: period ? period.endMonthDay : null,
          startPeriodName: period && period.startPeriodId ? this.periodService.getPeriodNameById(period.startPeriodId) : null,
          endPeriodName: period && period.endPeriodId ? this.periodService.getPeriodNameById(period.endPeriodId) : null,
          color: color
        };
      } else {
        // fill missing group metadata from other masses' periods if available
        const g = groups[weight];
        if ((!g.color || g.color === null) && period && period.id) {
          const gen = this.periodService.getGeneratedPeriodsByPeriodId(period.id);
          if (Array.isArray(gen) && gen.length > 0) {
            g.color = gen[0].color || g.color;
          }
        }
        if ((!g.startMonthDay || g.startMonthDay === null) && period && period.startMonthDay) {
          g.startMonthDay = period.startMonthDay;
        }
        if ((!g.endMonthDay || g.endMonthDay === null) && period && period.endMonthDay) {
          g.endMonthDay = period.endMonthDay;
        }
        if ((!g.startPeriodName || g.startPeriodName === null) && period && period.startPeriodId) {
          g.startPeriodName = this.periodService.getPeriodNameById(period.startPeriodId);
        }
        if ((!g.endPeriodName || g.endPeriodName === null) && period && period.endPeriodId) {
          g.endPeriodName = this.periodService.getPeriodNameById(period.endPeriodId);
        }
      }

      const flagMap: Record<string,string> = { hu: '🇭🇺', en: '🇬🇧', de: '🇩🇪', sk: '🇸🇰', ro: '🇷🇴' };
      const flag = flagMap[m.lang] || (m.lang ? String(m.lang).toUpperCase() : '');

      groups[weight].masses.push({
        id: m.id,
        title: m.title,
        rite: m.rite,
        startDate: m.startDate,
        rrule: m.rrule,
        readableRRule: this.getReadableRRule(m),
        lang: m.lang,
        flag: flag,
        types: m.types ? m.types : [],
        comment: m.comment,
        // include experiod ids and resolved period names for display
        experiod: m.experiod ? m.experiod : [],
        experiodNames: m.experiod ? m.experiod.map((pid: number) => this.periodService.getPeriodNameById(pid)).filter((n: any) => n) : []
      });
    });

    // Convert to array and sort by weight desc, and sort masses by startDate
    this.massListGrouped = Object.keys(groups).map(k => groups[parseInt(k)]).sort((a, b) => b.weight - a.weight);
    this.massListGrouped.forEach(g => g.masses.sort((x, y) => (x.startDate || '').localeCompare(y.startDate || '')));
  }
}
