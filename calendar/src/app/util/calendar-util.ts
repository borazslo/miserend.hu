import {CalendarOptions} from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import rrulePlugin from '@fullcalendar/rrule';
import huLocale from '@fullcalendar/core/locales/hu';
import {DialogEvent} from '../model/dialog-event';
import {LanguageCode} from '../enum/language-code';
import {Renum} from '../enum/recurrence';
import {Church} from '../model/church';
import {DateTimeUtil} from './date-time-util';
import {Day} from '../enum/day';
import {MassUtil} from './mass-util';
import {TranslateService} from '@ngx-translate/core';

export class CalendarUtil {

  public static getSimpleCalendarOptions(timeZone: string): CalendarOptions {
    return {
      plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, rrulePlugin],
      initialView: 'timeGridWeek',
      slotDuration: '01:00',
      timeZone: timeZone,
      locale: huLocale,
      firstDay: 1,
      height: '600px',
      headerToolbar: {
        left: 'prev,next',
        center: 'title',
        right: 'dayGridDay,timeGridWeek,dayGridMonth'
      },
      editable: false,
      dayMaxEvents: true,

      validRange: function(nowDate) {
        const currentYear = nowDate.getFullYear();
        return {
          start: new Date(currentYear - 1, 0, 1),
          end: new Date(currentYear + 2, 0, 1)
        };
      }
    }
  }

  public static getSimpleCalendarOptionsWithoutHeader(timeZone: string): CalendarOptions {
    const simpleCalendarOptions = this.getSimpleCalendarOptions(timeZone);
    simpleCalendarOptions.headerToolbar = false;
    return simpleCalendarOptions;
  }

  public static generateDialogEvent(church: Church, translate: TranslateService, date?: Date): DialogEvent {
    const titles = MassUtil.getTitles(church.rite);
    return {
      period: null,
      rite: church.rite,
      types: [],
      title: (titles && titles.length > 0) ? translate.instant(titles.at(0)!) : "",
      start: date ? date : new Date(),
      duration: {hours: 1},
      language: LanguageCode.HU,
      renum: Renum.NONE,
      selectedDays: date ? [DateTimeUtil.getShortEnDay(date)] : [Day.SU],
      comment: "",
      editOne: false
    };
  }

}
