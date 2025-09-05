import {Duration, MassType, Rite} from './mass';
import {LanguageCode} from '../enum/language-code';
import {Renum} from '../enum/recurrence';
import {Day} from '../enum/day';
import {GeneratedPeriod} from './generated-period';

export interface DialogEvent {
  /**
   * Az esemény periódusa (liturgikus időszaka).
   * Ha van, ez határozza meg a kezdeti és a végdátumot, melyben ismétlődik az esemény.
   */
  period: GeneratedPeriod | null;
  rite: Rite;
  types: MassType[];
  title: string;
  start: Date;
  duration: Duration;
  language: LanguageCode;
  renum: Renum;
  selectedDays: Day[];
  comment: string;
  editOne: boolean;
  exdate?: string[] | null;
  experiod?: number[] | null;
}
