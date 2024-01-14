<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Church;

use Illuminate\Database\Capsule\Manager as DB;

class Catalogue extends \App\Html\Html
{
    private $filterDiocese;
    private $filterDeanery;
    private $filterKeyword;
    private $filterStatus;
    private $orderBy;

    public function __construct()
    {
        parent::__construct();

        global $user;
        if (!$user->checkRole('miserend')) {
            throw new \Exception('Nincs jogosultságod megnézni a templomok listáját.');
        }

        $this->filterKeyword = ($this->input['church']['keyword'] ?? false);
        $this->filterDiocese = ($this->input['church']['egyhazmegye'] ?? false);
        $this->filterDeanery = ((isset($this->input['church']['espereskerulet']) && 0 != $this->input['church']['espereskerulet']) ? $this->input['church']['espereskerulet'] : false);
        $this->filterStatus = ($this->input['church']['status'] ?? false);
        $this->orderBy = ($this->input['church']['orderBy'] ?? 'updated_at DESC');

        $params = [
            'church[keyword]' => $this->filterKeyword,
            'church[egyhazmegye]' => $this->filterDiocese,
            'church[espereskerulet]' => $this->filterDeanery,
            'church[status]' => $this->filterStatus,
            'church[orderBy]' => $this->orderBy,
        ];
        foreach ($params as $key => &$param) {
            if ('' == $param || 0 == $param) {
                unset($params[$key]);
            }
        }

        $this->loadForm();
        $this->buildQuery();

        $url = \App\Pagination::qe($params);
        $this->pagination->set($this->search->count(), $url);

        $this->churches = $this->search->skip($this->pagination->skip)->take($this->pagination->take)->get();

        $accessibilityOSMTags = ['wheelchair', 'wheelchair:description', 'toilets:wheelchair', 'hearing_loop', 'disabled:description'];

        foreach ($this->churches as $church) {
            if ($church->osm) {
                foreach ($accessibilityOSMTags as $tag) {
                    if (\array_key_exists($tag, $church->osm->tagList) && '' != $church->osm->tagList[$tag]) {
                        $church->hasAccessibilityTag = true;
                        break;
                    }
                }
            }
        }
    }

    public function loadForm()
    {
        $this->form = \App\Form::religiousAdministrationSelection(['diocese' => $this->filterDiocese, 'deanery' => $this->filterDeanery]);

        $this->form['keyword'] = $this->filterKeyword;
        $this->form['status'] = [
            'name' => 'church[status]',
            'options' => [
                0 => 'Mind',
                'i' => 'csak engedélyezett templomok',
                'f' => 'áttekintésre várók',
                'n' => 'letiltott templomok',
                'Ru' => 'templomok új észrevétellel',
                'Rf' => 'templomok folyamatban lévő észrevétellel',
                'M0' => 'mise nélküliek',
            ],
            'selected' => $this->filterStatus,
        ];

        $this->form['orderBy'] = [
            'name' => 'church[orderBy]',
            'options' => [
                'updated_at DESC' => 'utolsó módosítás',
                'frissites DESC' => 'utolsó frissítés',
                'varos' => 'település',
                'nev' => 'név',
                'remarks.created_at' => 'utolsó észrevétel',
            ],
            'selected' => $this->orderBy,
        ];
    }

    public function buildQuery()
    {
        $search = \App\Model\Church::where('templomok.id', '>', 1);

        if ($this->filterKeyword) {
            $filterKeyword = '%'.$this->filterKeyword.'%';
            $search = $search->where(function ($query) use ($filterKeyword) {
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
            if ('Ru' == $this->filterStatus) {
                $search = $search->whereHas('remarks', function ($query) {
                    $query->where('allapot', 'u');
                });
            } elseif ('Rf' == $this->filterStatus) {
                $search = $search->whereHas('remarks', function ($query) {
                    $query->where('allapot', 'f');
                });
            }

            if (\in_array($this->filterStatus, ['i', 'f', 'n'])) {
                $search = $search->where('ok', $this->filterStatus);
            }

            if ('M0' == $this->filterStatus) {
                $search = $search->leftJoin(
                    DB::raw('('.
                            DB::table('misek')
                            ->select('id as mass_id', 'tid as mass_church_id', 'torles as mass_deleted_at')
                            ->whereRaw("`torles` = '0000-00-00 00:00:00'")
                            ->groupBy('mass_church_id')
                            ->orderBy('mass_deleted_at', 'ASC')
                            ->toSql()
                            .') mass  '), function ($j) {
                                $j->on('mass_church_id', '=', 'templomok.id');
                            }
                )
                        ->whereNull('mass_id');
            }
        }

        if ($this->orderBy) {
            if (\in_array($this->orderBy, [
                        'updated_at DESC', 'updated_at', 'frissites DESC', 'frissites',
                        'nev', 'ismertnev', 'varos'])) {
                $search = $search->orderByRaw($this->orderBy);
            } elseif ('remarks.created_at' == $this->orderBy) {
                $search = $search->leftJoin(
                    DB::raw('('.
                            DB::table('remarks')
                            ->select(['created_at as remark_created_at', 'church_id as remark_church_id'])
                            ->groupBy('remark_church_id')
                            ->orderBy('remark_created_at', 'DESC')->toSql()
                            .') first_remark '), 'remark_church_id', '=', 'templomok.id')
                        ->orderBy('remark_created_at', 'DESC');
            }
        }
        $this->search = $search;
    }
}
