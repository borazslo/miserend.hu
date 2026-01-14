import {Day} from '../../enum/day';

export interface RecurrenceRule {
  dtstart: string;
  until?: string | null;
  freq: 'daily' | 'weekly' | 'monthly' | 'yearly';
  count?: number | null;
  byweekno?: number[] | null;
  bysetpos?: number | null;
  byweekday?: Day[] | null;
  bymonth?: number | null;
  bymonthday?: number[] | null;
}
