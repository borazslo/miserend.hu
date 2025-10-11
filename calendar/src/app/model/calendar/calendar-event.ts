import {Duration} from '../mass';
import {RecurrenceRule} from './recurrence-rule';
import {ExtendedProps} from './extended-props';

export interface CalendarEvent {
  // id: string;
  title: string;
  duration?: Duration;
  rrule: RecurrenceRule;
  exrule?: RecurrenceRule[];
  exdate?: string[];
  extendedProps: ExtendedProps;
  className?: string;
  color?: string;
}
