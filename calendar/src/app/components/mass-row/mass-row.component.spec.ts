import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MassRowComponent } from './mass-row.component';

describe('MassRowComponent', () => {
  let component: MassRowComponent;
  let fixture: ComponentFixture<MassRowComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [MassRowComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(MassRowComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
