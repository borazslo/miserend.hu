import {FormBuilder, FormControl, FormGroup, ReactiveFormsModule} from '@angular/forms';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatAutocompleteModule} from '@angular/material/autocomplete';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';
import {MatCardModule} from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import {Component, OnInit} from '@angular/core';
import { MatExpansionModule } from '@angular/material/expansion';
import {SearchService} from '../../services/search.service';

@Component({
  selector: 'app-search',
  templateUrl: './search.component.html',
  imports: [
    ReactiveFormsModule,
    MatInputModule,
    MatSelectModule,
    MatCheckboxModule,
    MatDatepickerModule,
    MatNativeDateModule,
    MatButtonModule,
    MatCardModule,
    MatAutocompleteModule,
    MatExpansionModule
  ],
  styleUrls: ['./search.component.css']
})
export class SearchComponent implements OnInit {
  searchForm: FormGroup = new FormGroup({});

  // Opciós listák – később backendről is jöhetnek

  nyelvek:{ id: any; name: string }[] = [];
  zenek:{ id: number; name: string }[] = [];
  korosztalyok:{ id: number; name: string }[] = [];
  ritusok:{ id: any; name: string }[] = [];
  espereskeruletek:{ id: number; name: string }[] = [];
  orszagok:{ id: number; name: string }[] = [];
  megyek:{ id: number; name: string }[] = [];
  varosok:{ id: number; name: string }[] = [];
  egyhazmegyek:{ id: number; name: string }[] = [];

  // Szűrt verziók az autocomplete-hez
  filteredEgyhazmegyek = [...this.egyhazmegyek];
  filteredNyelvek = [...this.nyelvek];
  filteredRitusok = [...this.ritusok];


  constructor(private readonly searchService: SearchService,
              private fb: FormBuilder) {}

  ngOnInit(): void {

    this.searchService.getData().subscribe(data=>{
      this.egyhazmegyek = Object.values(data.egyhazmegyek);
      this.espereskeruletek = Object.values(data.espereskeruletek);
      this.orszagok = Object.values(data.orszagok);
      this.megyek = Object.values(data.megyek);
      this.varosok = Object.values(data.varosok);
      this.nyelvek = Object.values(data.languages);
      this.ritusok = Object.values(data.attributes).filter(attr => attr.group.trim().toLowerCase() == "liturgy");
      this.zenek = Object.values(data.attributes).filter(attr => attr.group.trim().toLowerCase() == "music");
      this.zenek.push({id: -1,name: "meghatározatlan"});
      this.korosztalyok = Object.values(data.attributes).filter(attr => attr.group.trim().toLowerCase() == "age");
      this.korosztalyok.push({id: -1,name: "meghatározatlan"});
    });
    this.searchForm = this.fb.group({
      // Templom adatok
      kulcsszo: [''],
      telepules: [''],
      ehm: [''],
      ehmSearch: [''],
      gorog: [false],
      nyelv: [''],
      nyelvSearch: [''],

      // Mise adatok
      mikor: [''],
      mikordatum: [new Date().toISOString().substring(0, 10)],
      mikor2: [''],
      startTime: ['08:00'],
      endTime: ['19:00'],
      zene: [''],
      korosztaly: [''],
      ritus: [''],
      ritusSearch: [''],
      ige: [false],
    });

    // Autocomplete szűrés – Egyházmegye
    this.searchForm.get('ehmSearch')?.valueChanges.subscribe(val => {
      this.filteredEgyhazmegyek = this.egyhazmegyek.filter(e =>
        e.name.toLowerCase().includes(val?.toLowerCase() || '')
      );
    });

    // Autocomplete szűrés – Nyelv
    this.searchForm.get('nyelvSearch')?.valueChanges.subscribe(val => {
      this.filteredNyelvek = this.nyelvek.filter(n =>
        n.name.toLowerCase().includes(val?.toLowerCase() || '')
      );
    });

    // Autocomplete szűrés – Rítus
    this.searchForm.get('ritusSearch')?.valueChanges.subscribe(val => {
      this.filteredRitusok = this.ritusok.filter(r =>
        r.name.toLowerCase().includes(val?.toLowerCase() || '')
      );
    });

  }


  // Keresés mise alapján
  searchMasses(): void {
    this.searchService.search('SearchResultsChurches', 40);

  }

  // Keresés templom alapján
  searchChurches(): void {
    this.searchService.search('SearchResultsChurches', 40);
  }

}
