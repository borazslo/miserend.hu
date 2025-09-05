import {Duration, MassType, Rite} from './mass';

export interface GeneratedMass {

  /**
   * Mise egyedi azonosítója
   */
  id?: number;

  /**
   * Templom egyedi azonosítója
   */
  churchId: number;

  /**
   * Mise egyedi azonosítója
   */
  massId?: number;

  /**
   * A konkrét mise kezdő időpontja (UTC-ben)
   * pl.: 2025-03-24T10:00:00
   */
  startDate: string;

  /**
   * Cím (naptárban megjelenő)
   */
  title: string;

  /**
   * A liturgia jellemzői.
   */
  types?: MassType[];

  /**
   * A liturgia rítusa. Default: a templom rítusa szerinti
   */
  rite: Rite;

  /**
   * A mise hossza. Alapértelmezetten 1 óra.
   * pl.: {hours: 1, minutes: 30}
   */
  duration?: Duration;

  /**
   * A mise nyelvének kétbetűs kódja. Default: hu
   */
  lang: string;

  /**
   * Szöveges megjegyzés a miséhez.
   */
  comment?: string;
}
