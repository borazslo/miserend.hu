import {Suggestion} from './suggestion';

export interface SuggestionPackage {
  id?: number;
  churchId?: number;
  senderName?: string;
  senderEmail?: string;
  senderUserId?: number;
  suggestions: Suggestion[];
  state: SuggestionState;
  createdAt: Date;
}

export enum SuggestionState {
  ACCEPTED = 'ACCEPTED',
  REJECTED = 'REJECTED',
  PENDING = 'PENDING'
}
