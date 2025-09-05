import {Mass, Rite} from './mass';

export interface Church {
  id: number;
  name: string;
  rite: Rite;
  timeZone: string;
  masses: Mass[];
}
