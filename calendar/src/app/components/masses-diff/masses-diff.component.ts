import {Component, Input, OnChanges, OnInit, SimpleChanges} from '@angular/core';
import {ReadableMass} from '../../model/readable-mass';
import {ScriptUtil} from '../../util/script-util';

@Component({
  selector: 'app-masses-diff',
  imports: [

  ],
  templateUrl: './masses-diff.component.html',
  styleUrl: './masses-diff.component.css'
})
export class MassesDiffComponent implements OnInit, OnChanges {
  @Input({ required: true }) origMass!: ReadableMass;
  @Input({ required: true }) newMass!: ReadableMass;
  experiodChanged : boolean = false;

  ngOnInit() {
    this.setExperiodChanged();
  }

  ngOnChanges(changes: SimpleChanges) {
    this.setExperiodChanged();
  }

  private setExperiodChanged() {
    this.experiodChanged = !ScriptUtil.deepEqual(this.origMass.experiod, this.newMass.experiod);
  }
}
