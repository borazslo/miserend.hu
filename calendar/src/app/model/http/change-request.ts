import {Mass} from '../mass';

export interface ChangeRequest {
  masses: Mass[];
  deletedMasses: number[];
}
