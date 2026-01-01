import { Injectable } from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs';
import {Mass} from './model/mass';
import {ChangeRequest} from './model/http/change-request';
import {SuggestionPackage, SuggestionState} from './model/suggestion-package';
import {Church} from './model/church';
import {environment} from '../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class EventService {

  constructor(private http: HttpClient) {}

  getChurch(churchId: number): Observable<Church> {
    return this.http.get<Church>(environment.apiUrl+'church/'+churchId);
  }

  saveChanges(churchId: number, masses: Mass[], deletedMasses: number[]): Observable<Mass[]> {
    const changeRequest: ChangeRequest = {
      masses: masses,
      deletedMasses: deletedMasses
    }
    return this.http.post<any[]>(environment.apiUrl+'masses/'+churchId, changeRequest);
  }

  sendToApprove(churchId: number, suggestionPackage: SuggestionPackage) {
    return this.http.post<any[]>(environment.apiUrl+'suggestions/church/'+churchId, suggestionPackage);
  }

  getSuggestions(churchId: number, state?: SuggestionState): Observable<SuggestionPackage[]> {
    let url = environment.apiUrl+'suggestions/church/'+churchId;
    if (state) {
      url += '/' + state;
    }
    return this.http.get<any[]>(url);
  }

  simpleAcceptSuggestionPackage(suggestionPackage: SuggestionPackage): Observable<{
    suggestionPackages: SuggestionPackage[];
    calendarMasses: Mass[]
  }> {
    const suffix = `suggestions/accept/${suggestionPackage.id}`;
    const body = {state: SuggestionState.ACCEPTED};
    return this.http.post<{ suggestionPackages: SuggestionPackage[]; calendarMasses: Mass[] }>(
      environment.apiUrl + suffix,
      body
    );
  }

  simpleRejectSuggestionPackage(suggestionPackage: SuggestionPackage): Observable<{
    suggestionPackages: SuggestionPackage[];
    calendarMasses: Mass[]
  }> {
    const suffix = `suggestions/reject/${suggestionPackage.id}`;
    const body = {state: SuggestionState.REJECTED};
    return this.http.post<{ suggestionPackages: SuggestionPackage[]; calendarMasses: Mass[] }>(
      environment.apiUrl + suffix,
      body
    );
  }

}
