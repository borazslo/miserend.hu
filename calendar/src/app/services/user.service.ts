import {Injectable} from '@angular/core';
import {catchError, map, Observable, of} from 'rxjs';
import {HttpClient} from '@angular/common/http';
import {User} from '../model/user';
import {environment} from '../../environments/environment';



@Injectable({
  providedIn: 'root'
})
export class UserService {
  defaultUser: User = {
    username: '',
    nickname: '',
    name: '',
    email: '',
    favorites: []
  };

  constructor(private http: HttpClient) {
  }

  loadUser(): Observable<any> {
    return this.http.get<User>(`${environment.apiUrl}caluser`)
      .pipe(
        map(user => {
          if (user) {
            return user;
          } else {
            return this.defaultUser;
          }
        }),
        catchError(() => of(this.defaultUser))
      );
  }
}
