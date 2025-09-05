import {Component, Input} from '@angular/core';
import {ReadableMass} from '../../model/readable-mass';

@Component({
  selector: 'app-mass-row',
  imports: [],
  templateUrl: './mass-row.component.html',
  styleUrl: './mass-row.component.css'
})
export class MassRowComponent {
  @Input({ required: true }) mass!: ReadableMass;
  @Input() showDetails: boolean = false;
}
