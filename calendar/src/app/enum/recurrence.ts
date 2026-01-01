export enum Renum {
  NONE = 'NONE',
  EVERY_WEEK = 'EVERY_WEEK',
  FIRST_WEEK = 'FIRST_WEEK',
  SECOND_WEEK = 'SECOND_WEEK',
  THIRD_WEEK = 'THIRD_WEEK',
  FOURTH_WEEK = 'FOURTH_WEEK',
  FIFTH_WEEK = 'FIFTH_WEEK',
  LAST_DAY_OF_MONTH = 'LAST_DAY_OF_MONTH',
  EVEN_WEEK = 'EVEN_WEEK',
  ODD_WEEK = 'ODD_WEEK',
}

export interface Recurrence {
  type: Renum,
  name: string,
  hint: string,
  multiDays: boolean
}

export const recurrences: Record<Renum, Recurrence> = {
  [Renum.NONE]: {
    type: Renum.NONE,
    name: 'Nincs ismétlődés',
    hint: 'Egyszeri alkalom.',
    multiDays: false
  },
  [Renum.EVERY_WEEK]: {
    type: Renum.EVERY_WEEK,
    name: 'Minden héten',
    hint: 'Minden héten ismétlődik, a megadott napokon.',
    multiDays: true
  },
  [Renum.FIRST_WEEK]: {
    type: Renum.FIRST_WEEK,
    name: 'Hónap első megadott napján',
    hint: 'Pl. elsőcsütörtök vagy elsőpéntek',
    multiDays: false
  },
  [Renum.SECOND_WEEK]: {
    type: Renum.SECOND_WEEK,
    name: 'Hónap második megadott napján',
    hint: 'Pl. minden hónap második vasárnapja',
    multiDays: false
  },
  [Renum.THIRD_WEEK]: {
    type: Renum.THIRD_WEEK,
    name: 'Hónap harmadik megadott napján',
    hint: 'Pl. minden hónap harmadik vasárnapja',
    multiDays: false
  },
  [Renum.FOURTH_WEEK]: {
    type: Renum.FOURTH_WEEK,
    name: 'Hónap negyedik megadott napján',
    hint: 'Pl. minden hónap negyedik vasárnapja',
    multiDays: false
  },
  [Renum.FIFTH_WEEK]: {
    type: Renum.FIFTH_WEEK,
    name: 'Hónap ötödik megadott napján',
    hint: 'Pl. minden hónap ötödik vasárnapja',
    multiDays: false
  },
  [Renum.LAST_DAY_OF_MONTH]: {
    type: Renum.LAST_DAY_OF_MONTH,
    name: 'Hónap utolsó megadott napján',
    hint: 'Pl. minden hónap utolsó hétfője',
    multiDays: false
  },
  [Renum.EVEN_WEEK]: {
    type: Renum.EVEN_WEEK,
    name: 'Páros héten',
    hint: 'Páros heteken ismétlődik a megadott nap(ok)on',
    multiDays: true
  },
  [Renum.ODD_WEEK]: {
    type: Renum.ODD_WEEK,
    name: 'Páratlan héten',
    hint: 'Páratlan heteken ismétlődik a megadott nap(ok)on',
    multiDays: true
  },
};
