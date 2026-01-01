export interface Period {
  id: number;
  name: string;
  weight: number;
  startMonthDay: string | null;
  endMonthDay: string | null;
  startPeriodId: number | null;
  endPeriodId: number | null;
  allInclusive: boolean | null;
  multiDay: boolean;
  selectable: boolean;
  specialType: SpecialType | null;
}

export enum SpecialType {
  EASTER = 'EASTER',
  CHRISTMAS = 'CHRISTMAS'
}
