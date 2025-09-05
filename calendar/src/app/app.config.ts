import {ApplicationConfig, importProvidersFrom, provideZoneChangeDetection} from '@angular/core';
import { provideRouter } from '@angular/router';

import { routes } from './app.routes';
import {HttpClient, provideHttpClient} from '@angular/common/http';
import {OverlayContainer} from '@angular/cdk/overlay';
import {InlineOverlayContainer} from './inline-overlay-container';
import { LOCALE_ID } from '@angular/core';
import { MAT_DATE_LOCALE } from '@angular/material/core';
import { registerLocaleData } from '@angular/common';
import localeHu from '@angular/common/locales/hu';
import {TranslateHttpLoader} from '@ngx-translate/http-loader';
import {TranslateLoader, TranslateModule} from '@ngx-translate/core';

registerLocaleData(localeHu);

export function HttpLoaderFactory(http: HttpClient) {
  return new TranslateHttpLoader(http, '/i18n/', '.json');
}


export const appConfig: ApplicationConfig = {
  providers: [
    provideZoneChangeDetection({ eventCoalescing: true }),
    provideRouter(routes),
    provideHttpClient(),
    {
      provide: OverlayContainer,
      useClass: InlineOverlayContainer
    },
    { provide: LOCALE_ID, useValue: 'hu' },
    { provide: MAT_DATE_LOCALE, useValue: 'hu' },
    importProvidersFrom(
      TranslateModule.forRoot({
        defaultLanguage: 'hu',
        loader: {
          provide: TranslateLoader,
          useFactory: HttpLoaderFactory,
          deps: [HttpClient]
        }
      }),
    )
  ],
};
