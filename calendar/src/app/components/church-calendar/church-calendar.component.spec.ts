import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ChurchCalendarComponent } from './church-calendar.component';

describe('ChurchCalendarComponent', () => {
  let component: ChurchCalendarComponent;
  let fixture: ComponentFixture<ChurchCalendarComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ChurchCalendarComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ChurchCalendarComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
