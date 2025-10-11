import {Injectable} from '@angular/core';
import {MatSnackBar} from '@angular/material/snack-bar';

@Injectable({
  providedIn: 'root'
})
export class MatSnackBarService {

  private static readonly DEFAULT_DURATION: number = 4000;
  public static readonly INFINITE_DURATION: number = 0;

  constructor(private readonly matSnackBar: MatSnackBar) {
  }

  public success(msg: string, duration?: number): void {
    this.show(msg, 'success-snackbar', duration);
  }

  public error(msg: string, duration?: number): void {
    this.show(msg, 'error-snackbar', duration);
  }

  public warning(msg: string): void {
    this.show(msg, 'warning-snackbar');
  }

  public show(
    msg: string,
    panelClass: string,
    duration: number = MatSnackBarService.DEFAULT_DURATION
  ): void {
    this.matSnackBar.open(msg, 'x', { panelClass, duration });
  }
}
