import {Injectable} from '@angular/core';
import {BehaviorSubject, map, Observable, of} from 'rxjs';
import {Period} from '../model/period';
import {GeneratedPeriod} from '../model/generated-period';
import {HttpClient} from '@angular/common/http';
import {PeriodsWrapper} from '../model/http/periods-wrapper';
import {PeriodYear} from '../model/period-year';
import {DateTime} from 'luxon';
import {ScriptUtil} from '../util/script-util';
import {DateTimeUtil} from '../util/date-time-util';
import {environment} from '../../environments/environment';


@Injectable({
  providedIn: 'root'
})
export class PeriodService {

  constructor(private http: HttpClient) {
    this.initPeriods();
  }

  readonly periods$ = new BehaviorSubject(<Period[]>([]));
  readonly generatedPeriods$ = new BehaviorSubject(<GeneratedPeriod[]>([]));


  private initPeriods() {

    this.http.get<PeriodsWrapper>(`${environment.apiUrl}periods`).subscribe(
      periodsWrapper => {
        this.periods$.next(periodsWrapper.periods);
        this.generatedPeriods$.next(DateTimeUtil.adjustEndDates(periodsWrapper.generatedPeriods));
      }
    );
  }

  public getPeriodsYear(): Observable<PeriodYear[]> {
    return this.http.get<PeriodYear[]>(`${environment.apiUrl}periods/edit`);
  }

  /**
   * Lekérdezzük azokat a generált időszakokat, amik:
   *  - kezdő dátuma ebben az évben van, vagy
   *  - vég dátuma ebben az évben van, ha kezdő dátuma nem ebben az évben van
   */
  private getSelectableGeneratedPeriodsByYear(year: number): Observable<GeneratedPeriod[]> {

    const generatedPeriods: GeneratedPeriod[] = this.generatedPeriods$.getValue();

    if (generatedPeriods) {
      let filteredGenPeriods = generatedPeriods
        .filter(gp => {
          const period = this.getPeriodById(gp.periodId);
          return ScriptUtil.isNotNull(period) && period.selectable;
        })
        .filter(p => {
          const startYear = parseInt(p.startDate.substring(0, 4), 10);

          const lEndDate = DateTime.fromISO(p.endDate, { zone: 'utc' });
          const previousDate = lEndDate.minus({ days: 1 });
          const endYear = previousDate.year;

          return startYear === year || (startYear !== year && endYear === year);
      });
      return of(filteredGenPeriods);
    }

    return of([]);
  }

  /**
   * Esemény felvételénél megjelenítjük az időszakokat
   * Ehhez lekérjük ezeket, és megpróbáljuk a kiválasztott dátum és súlyozás alapján rendezni
   * @param date
   */
  public getSelectableGeneratedPeriodsByDate(date: Date): Observable<GeneratedPeriod[]> {
    let generatedPeriodsByYear = this.getSelectableGeneratedPeriodsByYear(date.getFullYear());
    return generatedPeriodsByYear.pipe(
      map(periods => {
        const sorted = this.sortPeriods(periods, date);

        const seen = new Set<number>();
        return sorted.filter(period => {
          if (seen.has(period.periodId)) {
            return false;
          }
          seen.add(period.periodId);
          return true;
        });
      })
    );
  }

  private sortPeriods(periods: GeneratedPeriod[], date: Date): GeneratedPeriod[] {
    const dayStart = new Date(date);
    dayStart.setHours(0, 0, 0, 0);
    const dayEnd = new Date(dayStart);
    dayEnd.setHours(23, 59, 59, 999);

    periods.sort((a, b) => {
      const aHits = new Date(a.startDate) <= dayEnd && new Date(a.endDate) >= dayStart ? 1 : 0;
      const bHits = new Date(b.startDate) <= dayEnd && new Date(b.endDate) >= dayStart ? 1 : 0;

      if (aHits !== bHits) {
        return bHits - aHits;
      }

      if (b.weight !== a.weight) {
        return b.weight - a.weight;
      }

      const aDuration = new Date(a.endDate).getTime() - new Date(a.startDate).getTime();
      const bDuration = new Date(b.endDate).getTime() - new Date(b.startDate).getTime();

      return aDuration - bDuration;
    });

    return periods;
  }

  public getPeriodById(id?: number | null) {
    const currentPeriods = this.periods$.getValue();
    const period = currentPeriods.find(p => p.id === id);
    return period ?? null;
  }

  public getPeriodNameById(id: number): string {
    const currentPeriods = this.periods$.getValue();
    const periodName = currentPeriods.find(p => p.id === id)?.name;
    return periodName ?? "";
  }

  public getPeriodNamesByIds(ids: number[]): string[] {
    const periodNames: string[] = [];
    ids.forEach(id => {
      periodNames.push(this.getPeriodNameById(id));
    });
    return periodNames;
  }

  public getGeneratedPeriodsByPeriodId(periodId?: number) {
    const currentGeneratedPeriods = this.generatedPeriods$.getValue();
    const generatedPeriods = currentGeneratedPeriods.filter(p => p.periodId === periodId);
    return generatedPeriods ?? null;
  }

  public getCurrentGeneratedPeriodByPeriodId(periodId?: number | null, localDate?: Date): GeneratedPeriod | null {
    if (ScriptUtil.isNull(periodId) || ScriptUtil.isNull(localDate)) {
      return null;
    }

    const normalizeDate = (date: Date) => new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const targetDate = normalizeDate(localDate);

    const generatedPeriods = this.generatedPeriods$
      .getValue()
      .filter(p => {
        const start = normalizeDate(new Date(p.startDate));
        const end = normalizeDate(new Date(p.endDate));
        return (
          p.periodId === periodId &&
          start <= targetDate &&
          end > targetDate
        );
      });

    return generatedPeriods.length > 0 ? generatedPeriods[0] : null;
  }

  public saveData(periodYears: PeriodYear[]): Observable<any> {
    return this.http.post(`${environment.apiUrl}periods`, periodYears);
  }

  public generatePeriods(): Observable<any> {
    return this.http.post(`${environment.apiUrl}periods/generate`, {});
  }
}
