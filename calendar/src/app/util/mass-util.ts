import {Renum} from '../enum/recurrence';
import {DateTimeUtil} from './date-time-util';
import {Mass, Rite} from '../model/mass';
import {LanguageCode} from '../enum/language-code';
import {CalendarEvent} from '../model/calendar/calendar-event';
import {RecurrenceRule} from '../model/calendar/recurrence-rule';
import {Church} from '../model/church';
import {DialogEvent} from '../model/dialog-event';
import {GeneratedPeriod} from '../model/generated-period';
import {ScriptUtil} from './script-util';
import {TranslateService} from '@ngx-translate/core';
import {ChristmasDay} from "../enum/christmas-day";
import {EasterDay} from "../enum/easter-day";
import {Day} from "../enum/day";
import {SpecialType} from "../model/period";

export class MassUtil {

  private static tmpEventIdCounter: number = -1;

  private static readonly oddWeeks: number[] = [1,3,5,7,9,11,13,15,17,19,21,23,25,27,29,31,33,35,37,39,41,43,45,47,49,51];
  private static readonly evenWeeks: number[] = [2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32,34,36,38,40,42,44,46,48,50,52];

  public static createSimpleCalendarEventByDate(date: Date, churchRite: Rite, massId: number, translate: TranslateService): CalendarEvent {
    return {
      title: translate.instant(this.getSimpleTitleByRite(churchRite)),
      rrule: {
        dtstart: DateTimeUtil.getIsoString(date),
        freq: 'daily',
        count: 1,
      },
      extendedProps: {
        massId: massId
      }
    };
  }

  public static createSimpleMassByDate(date: Date, church: Church, massId: number, translate: TranslateService): Mass {
    return {
      id: massId,
      churchId: church.id,
      title: translate.instant(this.getSimpleTitleByRite(church.rite)),
      rite: church.rite,
      startDate: DateTimeUtil.getIsoString(date),
      lang: LanguageCode.HU
    };
  }

  public static createMass(calendarEvent: CalendarEvent, dialogEvent: DialogEvent, church: Church, massId: number): Mass {
    return {
      id: massId,
      churchId: church.id,
      ...(dialogEvent.period?.periodId && {periodId: dialogEvent.period?.periodId}),
      title: calendarEvent.title,
      ...(dialogEvent.types && {types: dialogEvent.types}),
      rite: dialogEvent.rite,
      startDate: calendarEvent.rrule.dtstart,
      ...(calendarEvent.duration && {duration: calendarEvent.duration}),
      ...(calendarEvent.rrule && {rrule: calendarEvent.rrule}),
      ...(calendarEvent.exdate && {exdate: calendarEvent.exdate}),
      lang: dialogEvent.language,
      comment: dialogEvent.comment
    };
  }

  public static createCalendarEvent(mass: Mass, periods: GeneratedPeriod[], recentExDates?:string[]): CalendarEvent[] {
    const calEvents: CalendarEvent[] = [];
    //ha nem egyszeri alkalom
    if (mass.rrule && mass.periodId) {
      periods
        .filter(gp => mass.periodId != null ? gp.periodId === mass.periodId: true)
        .forEach(period => {
        const calEvent: CalendarEvent = {
          //id: mass.id!.toString(),
          title: mass.title,
          rrule: ScriptUtil.clone(mass.rrule!),
          ...(mass.duration && {duration: mass.duration}),
          ...(mass.exdate && {exdate: mass.exdate}),
          ...(mass.experiod && {exrule: MassUtil.generateExRule(mass.rrule!, mass.experiod, periods)}),
          extendedProps: {
            massId: mass.id,
            recentExDates: recentExDates?.map(date=>date.slice(0, 10))
          },
          color:period.color
        };
        calEvent.rrule!.dtstart = period.startDate + mass.rrule!.dtstart.substring(10);
        if (mass.rrule!.until) {
          calEvent.rrule!.until = period.endDate;
        }
          calEvents.push(calEvent);
      });
    } else {
      const calEvent: CalendarEvent = {
        title: mass.title,
        rrule: {
          dtstart: mass.startDate,
          freq: 'daily',
          count: 1,
        },
        exdate: [],
        exrule: [],
        extendedProps: {
          massId: mass.id,
          recentExDates: recentExDates?.map(date=>date.slice(0, 10))
        },
      };
      calEvents.push(calEvent);
    }
    return calEvents;
  }

  public static createCalendarEvents(masses: Mass[], periods: GeneratedPeriod[], changes: number[], deletedMasses: number[], deletedDates:Map<number, string[]>): CalendarEvent[] {
    const calEvents: CalendarEvent[] = [];
    masses.forEach(mass   => {
      const calendarEvents = this.createCalendarEvent(mass, periods, deletedDates.get(mass.id) );
      if(mass.id < 0){
        calendarEvents.forEach(event =>{
          event.color = "#32CD32FF"
        })
      }
      else if (changes.includes(mass.id)){
        calendarEvents.forEach(event =>{
          event.color = "#ADFF2FFF"
        })
      }
      else if(deletedMasses.includes(mass.id)){
        calendarEvents.forEach(event =>{
          event.color = "#FF0000FF"
        })
      }
      calEvents.push(...calendarEvents);
    });
    return calEvents;
  }

  public static createEventByPeriods(event: CalendarEvent, periods: GeneratedPeriod[]): CalendarEvent[] {
    const events: CalendarEvent[] = [];
    for (const period of periods) {
      const calEvent = ScriptUtil.clone(event);
      calEvent.rrule.dtstart = period.startDate + calEvent.rrule.dtstart.substring(10);
      if (calEvent.rrule.until) {
        calEvent.rrule.until = period.endDate;
      }
      events.push(calEvent);
    }
    return events;
  }

  public static createEventByType(event: DialogEvent, massId: number, specialPeriodType?: SpecialType | null): CalendarEvent {
    const dtstart: string = DateTimeUtil.getIsoString(event.start, event.period?.startDate);
    const periodEnd = event.period?.endDate;

    let rrule: RecurrenceRule;

    if (specialPeriodType === SpecialType.CHRISTMAS) {
      rrule = {
        dtstart: dtstart,
        until: periodEnd,
        freq: 'monthly',
        bymonth: 12,
        bymonthday: [this.getWeekdayByChristmasDay(event.selectedChristmasDay!)],
      };
    } else if (specialPeriodType === SpecialType.EASTER) {
      rrule = {
        dtstart: dtstart,
        until: periodEnd,
        freq: 'weekly',
        byweekday: [this.getWeekdayByEasterDay(event.selectedEasterDay!)],
      };
    } else {
      switch (event.renum) {
        case Renum.NONE:
          return {
            title: event.title,
            ...(this.getDuration(event)),
            rrule: {
              dtstart: dtstart,
              freq: 'daily',
              count: 1,
            },
            ...(ScriptUtil.isNotNull(event.exdate) && {exdate: event.exdate}),
            extendedProps: {
              massId: massId
            }
          };
        case Renum.EVERY_WEEK:
          rrule = {
            dtstart: dtstart,
            until: periodEnd,
            freq: 'weekly',
            byweekday: event.selectedDays,
          };
          break;
        case Renum.FIRST_WEEK:
        case Renum.SECOND_WEEK:
        case Renum.THIRD_WEEK:
        case Renum.FOURTH_WEEK:
        case Renum.FIFTH_WEEK:
          rrule = {
            dtstart: dtstart,
            until: periodEnd,
            freq: 'monthly',
            bysetpos: this.bysetpos(event.renum),
            byweekday: event.selectedDays,
          };
          break;
        case Renum.LAST_DAY_OF_MONTH:
          rrule = {
            dtstart: dtstart,
            until: periodEnd,
            freq: 'monthly',
            bysetpos: -1,
            byweekday: event.selectedDays,
          };
          break;
        case Renum.ODD_WEEK:
        case Renum.EVEN_WEEK:
          rrule = {
            dtstart: dtstart,
            until: periodEnd,
            freq: 'weekly',
            byweekno: event.renum == Renum.ODD_WEEK ? this.oddWeeks : this.evenWeeks,
            byweekday: event.selectedDays,
          };
          break;
      }
    }

    return {
      title: event.title,
      ...this.getDuration(event),
      rrule: rrule,
      ...(ScriptUtil.isNotNull(event.exdate) && {exdate: event.exdate}),
      extendedProps: {
        massId: massId
      },
      color:event.period?.color
    };
  }

  public static massToDialogEvent(mass: Mass): DialogEvent {
    const renum: Renum = MassUtil.getRenumByMass(mass);
    return {
      period: null,
      rite: mass.rite,
      types: mass.types ? mass.types : [],
      title: mass.title,
      start: new Date(mass.startDate),
      duration: mass.duration ? mass.duration : {hours: 1},
      language: this.getLanguageCode(mass.lang),
      renum: renum,
      selectedDays: mass.rrule && mass.rrule.byweekday ? mass.rrule.byweekday : [],
      comment: mass.comment ? mass.comment : '',
      editOne: false,
      exdate: mass.exdate,
      experiod: mass.experiod
    };
  }

  public static massToDialogEventEditOne(mass: Mass, start: Date): DialogEvent {
    const dialogEvent = this.massToDialogEvent(mass);
    dialogEvent.editOne = true;
    dialogEvent.start = new Date(start);
    dialogEvent.selectedDays = [];
    dialogEvent.renum = Renum.NONE;
    dialogEvent.period = null;
    dialogEvent.exdate = null;
    dialogEvent.experiod = null;
    return dialogEvent;
  }

  private static bysetpos(renum: Renum): number {
    switch (renum) {
      case Renum.FIRST_WEEK: return 1;
      case Renum.SECOND_WEEK: return 2;
      case Renum.THIRD_WEEK: return 3;
      case Renum.FOURTH_WEEK: return 4;
      case Renum.FIFTH_WEEK: return 5;
      default: return 0;
    }
  }

  public static renumByPos(bysetpos: number): Renum | null {
    switch (bysetpos) {
      case -1: return Renum.LAST_DAY_OF_MONTH;
      case 1: return Renum.FIRST_WEEK;
      case 2: return  Renum.SECOND_WEEK;
      case 3: return  Renum.THIRD_WEEK;
      case 4: return  Renum.FOURTH_WEEK;
      case 5: return  Renum.FIFTH_WEEK;
      default: return null;
    }
  }

  public static christmasDayByMonthday(bymonthday: number[]): ChristmasDay | null {
    if (bymonthday.length !== 1) {
      return null;
    }

    switch (bymonthday[0]) {
      case 24: return ChristmasDay.DEC_24;
      case 25: return ChristmasDay.DEC_25;
      case 26: return ChristmasDay.DEC_26;
      default: return null;
    }
  }

  private static getSimpleTitleByRite(rite: Rite): string {
    switch (rite) {
      case Rite.TRADITIONAL:
        return "MASS_TITLE.TRADITIONAL_LATIN_MASS";
      case Rite.GREEK_CATHOLIC:
        return "MASS_TITLE.DIVINE_LITURGY";
      default:
        return "MASS_TITLE.HOLY_MASS";
    }
  }

  public static getSimpleTitle4Church(church: Church): string {
    return this.getSimpleTitleByRite(church.rite);
  }

  public static getTitles(rite: Rite): string[] {
    let titles: string[];

    if (rite === Rite.TRADITIONAL) {
      titles = [
        "MASS_TITLE.TRADITIONAL_LATIN_MASS",
        "MASS_TITLE.TRADITIONAL_MASS_OF_THE_LORD_S_SUPPER",
        "MASS_TITLE.TRADITIONAL_GOOD_FRIDAY_LITURGY",
        "MASS_TITLE.TRADITIONAL_EASTER_VIGIL"
      ];
    } else if (rite === Rite.GREEK_CATHOLIC) {
      titles = [
        "MASS_TITLE.DIVINE_LITURGY",
        "MASS_TITLE.LITURGY_OF_THE_PRESANCTIFIED_GIFTS",
      ];
    } else {
      titles = [
        "MASS_TITLE.HOLY_MASS",
        "MASS_TITLE.LITURGY_OF_THE_WORD",
        "MASS_TITLE.MASS_OF_THE_LORD_S_SUPPER",
        "MASS_TITLE.GOOD_FRIDAY_LITURGY",
        "MASS_TITLE.EASTER_VIGIL"
      ];
    }

    return titles;
  }

  private static getDuration(event: DialogEvent) {
    if (event.duration !== null) {
      if (
        event.duration.hours !== undefined && event.duration.hours != 1 ||
        (event.duration.days !== undefined && event.duration.days != 0) ||
        (event.duration.minutes !== undefined && event.duration.minutes != 0)
      ) {
        return {duration: event.duration};
      }
    }
    return undefined;
  }

  public static generateTmpMassId(): number {
    return this.tmpEventIdCounter--;
  }

  private static getLanguageCode(value: string): LanguageCode {
    return Object.values(LanguageCode).includes(value as LanguageCode) ? value as LanguageCode : LanguageCode.HU;
  }

  public static getRenumByMass(mass: Mass): Renum {
    const rrule = mass.rrule;
    if (ScriptUtil.isNull(mass.rrule)) {
      return Renum.NONE;
    }

    if (rrule?.freq === 'weekly') {
      if (rrule.byweekno && rrule.byweekno.length > 0) {
        const isEven = rrule.byweekno.every(n => n % 2 === 0);
        const isOdd = rrule.byweekno.every(n => n % 2 === 1);
        if (isEven) {
          return Renum.EVEN_WEEK;
        }
        if (isOdd) {
          return Renum.ODD_WEEK;
        }
        console.error('se nem páros, se nem páratlan heteken ismétlődik...');
        alert('se nem páros, se nem páratlan heteken ismétlődik...');
      }
      return Renum.EVERY_WEEK;
    }

    if (rrule?.freq === 'monthly' && ScriptUtil.isNotNull(rrule.bysetpos)) {
      let renumByPos = MassUtil.renumByPos(rrule.bysetpos);
      if (renumByPos != null) {
        return renumByPos;
      }
      console.error('havi ismétlődés, de valami gond van');
      alert('havi ismétlődés, de valami gond van...');
    }

    return Renum.NONE;
  }

  private static generateExRule(currentRrule: RecurrenceRule, experiod: number[], generatedPeriods: GeneratedPeriod[]): RecurrenceRule[] {
    const exrule: RecurrenceRule[] = [];

    experiod.forEach(periodId => {
      const filteredPeriods = generatedPeriods.filter(gp => gp.periodId === periodId);
      filteredPeriods.forEach(fp => {
        exrule.push({
          dtstart: DateTimeUtil.getExRuleDateTime(fp.startDate, currentRrule.dtstart),
          until: fp.endDate,
          freq: "daily",
        });
      });
    });

    return exrule;
  }

  public static getChristmasDayByMass(mass: Mass): ChristmasDay | null {
    if (ScriptUtil.isNull(mass.rrule) || mass.rrule.bymonth !== 12 || ScriptUtil.isNull(mass.rrule.bymonthday) ||
        mass.rrule.bymonthday.length !== 1) {
      return null;
    }

    const day = mass.rrule.bymonthday[0];

    switch (day) {
      case 24: return ChristmasDay.DEC_24;
      case 25: return ChristmasDay.DEC_25;
      case 26: return ChristmasDay.DEC_26;
    }

    return null;
  }

  public static getEasterDayByMass(mass: Mass): EasterDay | null {
    if (ScriptUtil.isNull(mass.rrule) || ScriptUtil.isNull(mass.rrule.byweekday) || mass.rrule.byweekday.length !== 1) {
      return null;
    }

    const day = mass.rrule.byweekday[0];

    switch (day) {
      case Day.TH: return EasterDay.TH;
      case Day.FR: return EasterDay.FR;
      case Day.SA: return EasterDay.SA;
      case Day.SU: return EasterDay.SU;
    }

    return null;
  }

  private static getWeekdayByEasterDay(selectedEasterDay: EasterDay): Day {
    switch (selectedEasterDay) {
      case EasterDay.TH: return Day.TH;
      case EasterDay.FR: return Day.FR;
      case EasterDay.SA: return Day.SA;
      case EasterDay.SU: return Day.SU;
    }
  }

  private static getWeekdayByChristmasDay(selectedChristmasDay: ChristmasDay): number {
    switch (selectedChristmasDay) {
      case ChristmasDay.DEC_24: return 24;
      case ChristmasDay.DEC_25: return 25;
      case ChristmasDay.DEC_26: return 26;
    }
  }
}
