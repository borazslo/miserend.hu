import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PeriodYearEditorComponent } from './period-year-editor.component';

describe('PeriodYearEditorComponent', () => {
  let component: PeriodYearEditorComponent;
  let fixture: ComponentFixture<PeriodYearEditorComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PeriodYearEditorComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PeriodYearEditorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
