import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MassesDiffComponent } from './masses-diff.component';

describe('MassesDiffComponent', () => {
  let component: MassesDiffComponent;
  let fixture: ComponentFixture<MassesDiffComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [MassesDiffComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(MassesDiffComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
