import {ChangeRequest} from './change-request';
import {SuggestionState} from '../suggestion-package';

export interface ModifiedSuggestionPackage {
  churchId: number;
  suggestionPackageId: number;
  changeRequest: ChangeRequest;
  state: SuggestionState;
}
