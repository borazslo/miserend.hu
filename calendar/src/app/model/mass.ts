import {RecurrenceRule} from './calendar/recurrence-rule';

export interface Mass {

  /**
   * Mise egyedi azonosítója
   */
  id: number;

  /**
   * Templom egyedi azonosítója
   */
  churchId: number;

  /**
   * Időszak egyedi azonosítója
   */
  periodId?: number | null;

  /**
   * Cím (naptárban megjelenő)
   */
  title: string;

  /**
   * A liturgia jellemzői.
   */
  types?: MassType[] | null;

  /**
   * A liturgia rítusa. Default: a templom rítusa szerinti
   */
  rite: Rite;

  /**
   * A mise (sorozat) kezdő időpontja.
   * pl.: 2025-03-24T10:00:00
   */
  startDate: string;

  /**
   * A mise hossza. Alapértelmezetten 1 óra.
   * pl.: {hours: 1, minutes: 30}
   */
  duration?: Duration | null;

  /**
   * Az ismétlődési szabály. A dtstart és az until az időszak alapján számolódik (ha van).
   * {
   *  dtstart: "2025-01-01T07:00:00",
   *  until: "2026-01-01"
   *  tzid: "Europe/Budapest",
   *  freq: "WEEKLY",
   *  byweekday: ["MO","TU","WE","TH","FR","SA","SU"]
   * }
   */
  rrule?: RecurrenceRule | null;

  /**
   * A megadott időpontokkal kezdődő események elrejtése.
   * Pl.: ['2025-04-02T07:00:00']
   */
  exdate?: string[] | null;

  /**
   * A megadott periódusok által lefedett időpontok elrejtése.
   */
  experiod?: number[] | null;

  /**
   * A mise nyelvének kétbetűs kódja. Default: hu
   */
  lang: string;

  /**
   * Szöveges megjegyzés a miséhez.
   */
  comment?: string | null;
}

export enum MassType {
  FAMILY = 'FAMILY',
  STUDENT = 'STUDENT',
  UNIVERSITY_YOUTH = 'UNIVERSITY_YOUTH',
  GUITAR = 'GUITAR',
  ORGAN = 'ORGAN',
  SILENT = 'SILENT',
  SINGER = 'SINGER',
}

export enum Rite {
  ROMAN_CATHOLIC = 'ROMAN_CATHOLIC',
  GREEK_CATHOLIC = 'GREEK_CATHOLIC',
  TRADITIONAL = 'TRADITIONAL'
}

export const RiteMassTypes: Record<Rite, MassType[]> = {
  [Rite.ROMAN_CATHOLIC]: [
    MassType.FAMILY,
    MassType.STUDENT,
    MassType.UNIVERSITY_YOUTH,
    MassType.GUITAR,
    MassType.ORGAN,
    MassType.SILENT
  ],
  [Rite.GREEK_CATHOLIC]: [
    MassType.FAMILY,
    MassType.STUDENT,
    MassType.UNIVERSITY_YOUTH
  ],
  [Rite.TRADITIONAL]: [
    MassType.SINGER,
    MassType.SILENT
  ],
}

export interface Duration {
  days?: number,
  hours?: number,
  minutes?: number
}
