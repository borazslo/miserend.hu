import { Injectable } from '@angular/core';

@Injectable({ providedIn: 'root' })
export class EditConfirmationService {
  private confirmed: boolean = false;
  private messageText: string = 'Szeretnéd szerkeszteni a naptárat, hogy a végén javítási javaslatként beküldhesd a megfelelő miserendet?';

  isConfirmed(): boolean {
    return this.confirmed;
  }

  confirm(): void {
    this.confirmed = true;
  }

  getMessage(): string {
    return this.messageText;
  }

  setMessage(text: string): void {
    this.messageText = text;
  }
}
