import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {environment} from '../../environments/environment';
import {HttpParams} from '@angular/common/http';

@Injectable({ providedIn: 'root' })
export class SearchService {

  constructor(private http: HttpClient) {}

  getData() {
    return this.http.get<SearchData>(`${environment.apiUrl}search`);
  }

  public generateMasses(years: number[], tid: number) {
    let params = new HttpParams();
    params = params.append('tids[]', tid.toString());
    years.forEach(year => {
      params = params.append('years[]', year.toString());
    });

    return this.http.put(`${environment.apiUrl}generate`, null, { params });
  }

  public search(q: string, templom: any) {
    this.http.post(`${environment.apiUrl}search`, {
      params: { q: q, templom: templom }
    }).subscribe(res => {
      console.log(res);
    });
  }
}

export interface SearchData {
  attributes: { [key: string]: { id: any; name: string ; group: string} };
  languages: { [key: string]: { id: any; name: string } };
  egyhazmegyek: { [key: string]: { id: number; name: string } };
  espereskeruletek: { [key: string]: { id: number; name: string } };
  orszagok: { [key: string]: { id: number; name: string } };
  megyek: { [key: string]: { id: number; name: string } };
  varosok: { [key: string]: { id: number; name: string } };
}
