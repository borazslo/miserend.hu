import {PeriodYearEdit} from './period-year-edit';

export interface PeriodYearWrapper {
  year: number;
  minDate: Date;
  maxDate: Date;
  defaultDate: Date;
  periodYearEdits: PeriodYearEdit[];
}
