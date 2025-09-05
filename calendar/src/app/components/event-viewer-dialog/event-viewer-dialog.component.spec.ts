import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EventViewerDialogComponent } from './event-viewer-dialog.component';

describe('EventViewerDialogComponent', () => {
  let component: EventViewerDialogComponent;
  let fixture: ComponentFixture<EventViewerDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [EventViewerDialogComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EventViewerDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
