import { Routes } from '@angular/router';
import {SuggestionsComponent} from './components/suggestions/suggestions.component';
import {ChurchComponent} from './components/church/church.component';
import {EditScheduleComponent} from './components/edit-schedule/edit-schedule.component';
import {PeriodYearEditorComponent} from './components/period-year-editor/period-year-editor.component';
import {SearchComponent} from './components/search/search.component';

export const routes: Routes = [
  { path: 'templom/:id', component: ChurchComponent },
  { path: 'templom/:id/editschedule', component: EditScheduleComponent },
  { path: 'templom/:id/javaslatok', component: SuggestionsComponent },
  { path: 'periodyeareditor', component: PeriodYearEditorComponent },
  { path: '', component: SearchComponent },
];
