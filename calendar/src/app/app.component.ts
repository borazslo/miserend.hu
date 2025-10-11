import {Component, ViewEncapsulation} from '@angular/core';
import { RouterOutlet } from '@angular/router';
import {TranslateService} from '@ngx-translate/core';
import {MatProgressSpinner} from '@angular/material/progress-spinner';
import {SpinnerService} from './services/spinner.service';
import {environment} from '../environments/environment';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, MatProgressSpinner],
  templateUrl: './app.component.html',
  styleUrls: ['../styles.scss', './app.component.css'],
  encapsulation: ViewEncapsulation.ShadowDom
})
export class AppComponent {
  title = 'mcal';
  env = environment;

  constructor(
    private readonly translateService: TranslateService,
    public readonly spinnerService: SpinnerService,
  ) {
    this.translateService.setDefaultLang('hu');
    this.translateService.use('hu');
  }
}
