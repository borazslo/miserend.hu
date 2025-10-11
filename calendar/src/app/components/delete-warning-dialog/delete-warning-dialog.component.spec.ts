import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DeleteWarningDialogComponent } from './delete-warning-dialog.component';

describe('EventEditDialogComponent', () => {
  let component: DeleteWarningDialogComponent;
  let fixture: ComponentFixture<DeleteWarningDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DeleteWarningDialogComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DeleteWarningDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
