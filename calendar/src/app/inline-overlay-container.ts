import { OverlayContainer } from '@angular/cdk/overlay';
import { Injectable } from '@angular/core';

@Injectable()
export class InlineOverlayContainer extends OverlayContainer {
  override _createContainer(): void {
    const shadowRoot = document.querySelector('app-root')?.shadowRoot;
    if (shadowRoot) {
      this._containerElement = document.createElement('div');
      this._containerElement.classList.add('cdk-overlay-container');
      shadowRoot.appendChild(this._containerElement);
    } else {
      super._createContainer();
    }
  }
}
