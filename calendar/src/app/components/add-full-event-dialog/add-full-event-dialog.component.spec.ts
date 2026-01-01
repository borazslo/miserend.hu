import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AddFullEventDialogComponent } from './add-full-event-dialog.component';

describe('EventEditDialogComponent', () => {
  let component: AddFullEventDialogComponent;
  let fixture: ComponentFixture<AddFullEventDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AddFullEventDialogComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AddFullEventDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
