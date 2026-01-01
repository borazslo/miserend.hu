import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AddSimpleEventDialogComponent } from './add-simple-event-dialog.component';

describe('AddSimpleEventDialogComponent', () => {
  let component: AddSimpleEventDialogComponent;
  let fixture: ComponentFixture<AddSimpleEventDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AddSimpleEventDialogComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AddSimpleEventDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
