import {Day} from '../../enum/day';

export interface RecurrenceRule {
  dtstart: string;
  until?: string | null;
  freq: 'daily' | 'weekly' | 'monthly';
  count?: number | null;
  byweekno?: number[] | null;
  bysetpos?: number | null;
  byweekday?: Day[] | null;
}
