import {Component, HostListener, OnInit, ViewChild} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ChurchCalendarComponent} from '../church-calendar/church-calendar.component';
import {Church} from '../../model/church';
import {Mass} from '../../model/mass';
import {ActivatedRoute} from '@angular/router';
import {EventService} from '../../event.service';
import {SpinnerService} from '../../services/spinner.service';

@Component({
  selector: 'app-church',
  imports: [
    CommonModule,
    ChurchCalendarComponent
  ],
  templateUrl: './church.component.html',
  styleUrl: './church.component.css'
})
export class ChurchComponent implements OnInit {

  public dataLoaded: boolean = false;
  public currentChurch?: Church;
  public masses: Map<number, Mass> = new Map();
  public loadError: string | null = null;

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
    this.eventService.getChurch(churchId).subscribe({
      next: (church: Church) => {
        this.currentChurch = church;
        this.masses = new Map(church.masses.map(e => [e.id!, e]));
        this.loadError = null;
        this.dataLoaded = true;
        this.spinnerService.hide();
      },
      error: (err: any) => {
        console.error('Failed to load church data', err);
        // Show a concise, user-friendly error message. Keep dataLoaded true so template can render the message.
        this.loadError = 'Nem sikerült betölteni a templom adatait, mert az adatszolgáltató oldal nem elérhető. Elnézést kérünk.';
        this.currentChurch = undefined;
        this.masses = new Map();
        this.dataLoaded = true;
        this.spinnerService.hide();
      }
    });
  }

  @HostListener('window:beforeunload', ['$event'])
  handleBeforeUnload(event: BeforeUnloadEvent) {
    if (this.churchCalendar.hasUnsavedChanges) {
      event.preventDefault();
    }
  }
}
