export interface Suggestion {
  id?: number | null;
  packageId?: number | null;
  periodId?: number | null;
  massId?: number | null;
  massState: MassState;
  changes: any;
}

export enum MassState {
  NEW = 'NEW',
  DELETED = 'DELETED',
  MODIFIED = 'MODIFIED'
}
