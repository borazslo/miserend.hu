import {Injectable} from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class SpinnerService {

  spinnerVisible: boolean = false;

  show(): void {
    setTimeout(() => {
      this.spinnerVisible = true;
    });
  }

  hide(): void {
    setTimeout(() => {
      this.spinnerVisible = false;
    });
  }
}
