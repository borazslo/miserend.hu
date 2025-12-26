import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { EventService } from '../../event.service';
import { Church } from '../../model/church';
import { ChurchCalendarComponent } from '../church-calendar/church-calendar.component';

@Component({
  selector: 'app-calendar-widget',
  standalone: true,
  imports: [CommonModule, ChurchCalendarComponent],
  templateUrl: './widget.component.html',
  styleUrls: ['./widget.component.css']
})
export class WidgetComponent implements OnInit {
  public loading = true;
  public error: string | null = null;
  public church?: Church;
  public massesMap: Map<number, any> = new Map();

  constructor(private route: ActivatedRoute, private eventService: EventService) {}

  ngOnInit(): void {
    // Prefer route parameter ':id' when widget is mounted on /templom/:id/widget
    const idParam = this.route.snapshot.paramMap.get('id');
    if (!idParam) {
      this.error = 'church_id missing';
      this.loading = false;
      return;
    }
    const churchId = parseInt(idParam, 10);
    if (isNaN(churchId)) {
      this.error = 'invalid church_id';
      this.loading = false;
      return;
    }

    this.eventService.getChurch(churchId).subscribe({
      next: (c) => {
        this.church = c;
        // convert masses array to Map for compatibility with church-calendar inputs
        if (Array.isArray(c.masses)) {
          this.massesMap = new Map<number, any>(c.masses.map((m: any) => [m.id, m]));
        }
        this.loading = false;
      },
      error: (err) => {
        console.error('widget getChurch error', err);
        this.error = 'Nem sikerült betölteni a templom adatait.';
        this.loading = false;
      }
    });
  }
}
