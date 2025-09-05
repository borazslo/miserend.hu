import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EditScheduleComponent } from './edit-schedule.component';

describe('EditScheduleComponent', () => {
  let component: EditScheduleComponent;
  let fixture: ComponentFixture<EditScheduleComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [EditScheduleComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EditScheduleComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
