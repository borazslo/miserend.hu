<?php

namespace Html\Church;

use Illuminate\Database\Capsule\Manager as DB;

class Catalogue extends \Html\Html {

    private $filterDiocese;
    private $filterDeanery;
    private $filterKeyword;
    private $filterStatus;
    private $orderBy;

    public function __construct() {
        parent::__construct();

        global $user;
        if (!$user->checkRole('miserend')) {
            throw new \Exception('Nincs jogosultságod megnézni a templomok listáját.');
        }
               
        $this->filterKeyword = (isset($this->input['keyword']) ? $this->input['keyword'] : false);
        $this->filterDiocese = (isset($this->input['egyhazmegye']) ? $this->input['egyhazmegye'] : false);
        $this->filterDeanery = ((isset($this->input['espereskerulet']) AND $this->input['espereskerulet'] != 0 ) ? $this->input['espereskerulet'] : false);
        $this->filterStatus = (isset($this->input['status']) ? $this->input['status'] : false);
        $this->orderBy = (isset($this->input['orderBy']) ? $this->input['orderBy'] : 'updated_at DESC');
        
        $params = [
            'keyword' => $this->filterKeyword,
            'egyhazmegye' => $this->filterDiocese,
            'espereskerulet' => $this->filterDeanery,
            'status' => $this->filterStatus,
            'orderBy' => $this->orderBy
        ];
        foreach ($params as $key => &$param) {
            if ($param == '' or $param == 0)
                unset($params[$key]);
        }

        $this->loadForm();
        $this->buildQuery();

        $url = \Pagination::qe($params);
        $this->pagination->set($this->search->count(), $url);

        $this->churches = $this->search->skip($this->pagination->skip)->take($this->pagination->take)->get();
		
		$accessibilityOSMTags = ['wheelchair', 'wheelchair:description','toilets:wheelchair','hearing_loop','disabled:description'];
		
		foreach($this->churches as $church) {
			if($church->osm) {
					foreach($accessibilityOSMTags as $tag) {
						if(array_key_exists($tag,$church->osm->tagList) AND $church->osm->tagList[$tag] != '' ) {
								$church->hasAccessibilityTag = true;
								break;
						}
					}			
			}			
		}
		
        
    }

    function loadForm() {
        // FIXME for Issue #257
        $this->form = \Form::religiousAdministrationSelection(['diocese' => $this->filterDiocese, 'deanery' => $this->filterDeanery]);

        
        $this->form['dioceses']['name'] = 'egyhazmegye';
        $this->form['deaneries']['name'] = 'espereskerulet';
        
        $this->form['keyword'] = $this->filterKeyword;
        $this->form['status'] = [
            'name' => 'status',
            'options' => [
                0 => 'Mind',
                'i' => 'csak engedélyezett templomok',
                'f' => 'áttekintésre várók',
                'n' => 'letiltott templomok',
                'Rnj' => 'templomok nem jóváhagyott észrevétellel',
                'Ru' => 'templomok új észrevétellel',
                'Rf' => 'templomok folyamatban lévő észrevétellel',
                'M0' => 'mise nélküliek'
            ],
            'selected' => $this->filterStatus
        ];

        $this->form['orderBy'] = [
            'name' => 'orderBy',
            'options' => [
                'updated_at DESC' => 'utolsó módosítás',
                'frissites DESC' => 'utolsó frissítés',
                'varos' => 'település',
                'nev' => 'név',
                'remarks.created_at' => 'utolsó észrevétel',
            ],
            'selected' => $this->orderBy
        ];
    }

    function buildQuery() {
        // FIXME for Issue #257
        $search = \Eloquent\Church::where('templomok.id', '>', 1);

        if ($this->filterKeyword) {
            $filterKeyword = '%' . $this->filterKeyword . '%';
            $search = $search->where(function($query) use ($filterKeyword) {
                $query->where('nev', 'LIKE', $filterKeyword)->
                        orWhere('varos', 'LIKE', $filterKeyword)->
                        orWhere('ismertnev', 'LIKE', $filterKeyword);
            });

        }

        if ($this->filterDiocese) {
            $search = $search->where('egyhazmegye', $this->filterDiocese);
            if ($this->filterDeanery) {
                $search = $search->where('espereskerulet', $this->filterDeanery);
            }
        }

        if ($this->filterStatus) {
            if ($this->filterStatus == 'Ru') {
                $search = $search->whereHas('remarks', function ($query) {
                    $query->where('allapot', 'u');
                });
            } else if ($this->filterStatus == 'Rf') {
                $search = $search->whereHas('remarks', function ($query) {
                    $query->where('allapot', 'f');
                });
            } else if ($this->filterStatus == 'Rnj') {
                $search = $search->whereHas('remarks', function ($query) {
                    $query->where('allapot', '!=', 'j');
                });
            }

            if (in_array($this->filterStatus, ['i', 'f', 'n'])) {
                $search = $search->where('ok', $this->filterStatus);
            }

            if ($this->filterStatus == 'M0') {
                $search = $search->leftJoin(
                                DB::raw("(" .
                                        DB::table('misek')
                                        ->select('id as mass_id', 'tid as mass_church_id', 'torles as mass_deleted_at')
                                        ->whereRaw("`torles` = '0000-00-00 00:00:00'")
                                        ->groupBy('mass_church_id')
                                        ->orderBy('mass_deleted_at', 'ASC')
                                        ->toSql()
                                        . ") mass  ")
                                , function ($j) {
                            $j->on('mass_church_id', '=', 'templomok.id');
                        }
                        )
                        ->whereNull('mass_id');
            }
        }

        if ($this->orderBy) {
            if (in_array($this->orderBy, [
                        'updated_at DESC', 'updated_at', 'frissites DESC', 'frissites',
                        'nev', 'ismertnev', 'varos'])) {
                $search = $search->orderByRaw($this->orderBy);
            } elseif ($this->orderBy == 'remarks.created_at') {
                $search = $search->leftJoin(
                                DB::raw("(" .
                                        DB::table('remarks')
                                        ->select(['created_at as remark_created_at', 'church_id as remark_church_id'])
                                        ->groupBy('remark_church_id')
                                        ->orderBy('remark_created_at', 'DESC')->toSql()
                                        . ") first_remark ")
                                , 'remark_church_id', '=', 'templomok.id')
                        ->orderBy('remark_created_at', 'DESC');
            }
        }
        $this->search = $search;
    }

}
