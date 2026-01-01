import {Component, HostListener, OnInit, ViewChild} from '@angular/core';
import {ChurchCalendarComponent} from '../church-calendar/church-calendar.component';
import {ActivatedRoute} from '@angular/router';
import {EventService} from '../../event.service';
import {SpinnerService} from '../../services/spinner.service';
import {Mass} from '../../model/mass';
import {Church} from '../../model/church';

@Component({
  selector: 'app-edit-schedule',
  imports: [
    ChurchCalendarComponent
  ],
  templateUrl: './edit-schedule.component.html',
  styleUrl: './edit-schedule.component.css'
})
export class EditScheduleComponent implements OnInit {

  public dataLoaded: boolean = false;
  public currentChurch?: Church;
  public masses: Map<number, Mass> = new Map();

  @ViewChild('churchCalendar') churchCalendar!: ChurchCalendarComponent;

  constructor(
    private readonly activatedRoute: ActivatedRoute,
    private readonly eventService: EventService,
    private readonly spinnerService: SpinnerService,
  ) {
  }

  ngOnInit() {
    this.spinnerService.show();
    this.initEvents();
  }

  private initEvents() {
    const churchId: number = +this.activatedRoute.snapshot.params['id'];
    this.eventService.getChurch(churchId).subscribe((church: Church) => {
      this.currentChurch = church;
      this.masses = new Map(church.masses.map(e => [e.id!, e]));
      this.dataLoaded = true;
    });
  }

  @HostListener('window:beforeunload', ['$event'])
  handleBeforeUnload(event: BeforeUnloadEvent) {
    if (this.churchCalendar.hasUnsavedChanges) {
      event.preventDefault();
    }
  }

}
