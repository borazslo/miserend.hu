import { Routes } from '@angular/router';
import {SuggestionsComponent} from './components/suggestions/suggestions.component';
import {ChurchComponent} from './components/church/church.component';
import {EditScheduleComponent} from './components/edit-schedule/edit-schedule.component';
import {PeriodYearEditorComponent} from './components/period-year-editor/period-year-editor.component';
import {SearchComponent} from './components/search/search.component';
import { WidgetComponent } from './components/widget/widget.component';

export const routes: Routes = [
  // Specific sub-routes must come before the generic 'templom/:id' route
  { path: 'templom/:id/widget', component: WidgetComponent },
  { path: 'templom/:id/editschedule', component: EditScheduleComponent },
  { path: 'templom/:id/javaslatok', component: SuggestionsComponent },
  { path: 'templom/:id', component: ChurchComponent },
  { path: 'periodyeareditor', component: PeriodYearEditorComponent },
  { path: '', component: SearchComponent },
];
