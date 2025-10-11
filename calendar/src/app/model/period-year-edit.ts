export interface PeriodYearEdit {
  id: number;
  periodId: number;
  startYear: number;
  startDate: Date | null;
  endDate: Date | null;
  periodName: string;
  multiDay: boolean;
}
