<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class PeriodYearEditor extends Html {

    // Tároljuk a periódusokat a sablon számára
    public $periods = [];

    public function __construct($path) {
        global $user;

        if (!$user->checkRole('miserend')) {
            throw new \Exception('Nincs jogosultságod megnézni az események listáját.');
        }

        $periods = DB::table('cal_periods as p')
            ->leftJoin('cal_periods as sp', 'p.start_period_id', '=', 'sp.id')
            ->leftJoin('cal_periods as ep', 'p.end_period_id', '=', 'ep.id')
            ->leftJoin('cal_masses as m', 'm.period_id', '=', 'p.id')
            ->select(
                'p.*',
                'sp.name as start_period_name',
                'ep.name as end_period_name',
                DB::raw('COUNT(m.id) as masses_count'),
                DB::raw('COUNT(DISTINCT m.church_id) as churches_count')
            )
            ->groupBy('p.id')
            ->orderBy('p.weight', 'desc')
            ->orderBy('p.name', 'asc')
            ->get();

        // Ha további aggregációt szeretnénk (start_period_id / end_period_id esetén),
        // menjünk még egyszer végig a $periods-on és adjuk hozzá a gyermek periódusok
        // mass/church számait a megcélzott periodokhoz.
        // Először készítünk egy id->period referencia tömböt, és inicializáljuk
        // az extra_mass_count és extra_churches_count mezőket.
        $periodById = [];
        foreach ($periods as $p) {
            $periodById[$p->id] = $p;
            if (!isset($periodById[$p->id]->extra_mass_count)) {
                $periodById[$p->id]->extra_mass_count = 0;
            }
            if (!isset($periodById[$p->id]->extra_churches_count)) {
                $periodById[$p->id]->extra_churches_count = 0;
            }
        }

        // Most végigmegyünk újra, és ha a periodnak van start_period_id vagy end_period_id,
        // akkor a jelenlegi period tömegszámát (masses_count) hozzáadjuk a hivatkozott period
        // extra_mass_count mezőjéhez, illetve hozzáadjuk a churches_count-ot az extra_churches_count-hoz.
        foreach ($periods as $p) {
            $massCnt = isset($p->masses_count) ? (int)$p->masses_count : 0;
            $churchCnt = isset($p->churches_count) ? (int)$p->churches_count : 0;

            // Ha start_period_id meg van adva és létezik a célperiod
            if (!empty($p->start_period_id) && isset($periodById[$p->start_period_id])) {
                // Ne adjuk hozzá önmagához (védelem, ha valaki rosszul állította be)
                if ($p->start_period_id != $p->id) {
                    $periodById[$p->start_period_id]->extra_mass_count += $massCnt;
                    $periodById[$p->start_period_id]->extra_churches_count += $churchCnt;
                }
            }

            // Ha end_period_id meg van adva és létezik a célperiod
            if (!empty($p->end_period_id) && isset($periodById[$p->end_period_id])) {
                if ($p->end_period_id != $p->id) {
                    $periodById[$p->end_period_id]->extra_mass_count += $massCnt;
                    $periodById[$p->end_period_id]->extra_churches_count += $churchCnt;
                }
            }
        }

        $this->periods = $periods;


    }


}
