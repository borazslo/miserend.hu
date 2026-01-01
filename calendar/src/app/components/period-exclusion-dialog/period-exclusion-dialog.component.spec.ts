import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PeriodExclusionDialogComponent } from './period-exclusion-dialog.component';

describe('PeriodExclusionDialogComponent', () => {
  let component: PeriodExclusionDialogComponent;
  let fixture: ComponentFixture<PeriodExclusionDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PeriodExclusionDialogComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PeriodExclusionDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
