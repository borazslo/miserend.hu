<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html;

use App\Legacy\Jqplots;
use App\Legacy\Model\Church;
use App\Legacy\Model\ChurchHolder;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Stat extends Html
{
    public function stat(Request $request): Response
    {
        $vars = [
            'title' => 'Statisztika',
            'pageTitle' => 'Statisztika | Miserend',
            'stats' => [],
        ];

        $stat = $this->getUpdatesOnActiveInLastYear();
        $vars['stats'][$stat->id] = $stat;
        $stat = $this->getUpdatesInLastYear();
        $vars['stats'][$stat->id] = $stat;
        $vars['s4'] = $this->getChurchHolderStats();
        $vars['s2'] = $this->getUserStats();
        $vars['s5'] = $this->getApiStat();
        $vars['magyar'] = $this->getActiveHungarianChurches();
        $vars['accessibility'] = $this->getChurchAccessibility();
        // $vars['osmtags'] = $this->getOsmTags(); TODO kideriteni miert olyan veszett lassu

        return $this->render('stat.twig', $vars);
    }

    /**
     * Templomok frissítettsége + észrevételek: Elmúlt év.
     */
    protected function getUpdatesOnActiveInLastYear(): Jqplots
    {
        $stat = new Jqplots('chart_templomaink');
        $stat->title = 'Aktív misézőhelyeink frissítettsége';
        $stat->labels = ['aktív misézőhelyek, amik akkor frissültek utoljára', 'beküldött észrevételek száma az adott időben'];
        $stat->axes['xaxis'] = [];

        $stat->data = [0 => [], 1 => []];

        $churches = Church::where('ok', 'i')
            ->where('miseaktiv', 1)
            ->countByUpdatedYear()
            ->get();

        foreach ($churches as $church) {
            $stat->data[0][] = [(int) $church->updated_year, $church->count_updated_year];
        }

        $remarks = \App\Legacy\Model\Remark::countByCreatedYear()->get();
        foreach ($remarks as $remark) {
            if ($remark->created_year > 0) {
                $stat->data[1][] = [(int) $remark->created_year, $remark->count_created_year];
            }
        }
        $stat->prepare_html();
        $stat->prepare_script();

        return $stat;
    }

    /**
     * Templomok frissítettsége + észrevételek: Elmúlt év.
     */
    protected function getUpdatesInLastYear(): Jqplots
    {
        $stat = new Jqplots('chartN');
        $stat->title = 'Az elmúlt 12 hónap frissítései';
        $stat->labels = ['aktív misézőhelyek, amik akkor frissültek utoljára', 'beküldött észrevételek száma az adott időben'];

        $churches = Church::where('ok', 'i')
            ->where('miseaktiv', 1)
            ->countByUpdatedMonth()
            ->where('frissites', '>', date('Y-m-d', strtotime('-1 year')))
            ->get();
        $stat->data = [0 => [], 1 => []];
        foreach ($churches as $church) {
            $stat->data[0][] = [$church->updated_month, $church->count_updated_month];
        }
        $remarks = \App\Legacy\Model\Remark::countByCreatedMonth()
            ->where('created_at', '>', date('Y-m-d', strtotime('-1 year')))
            ->get();
        foreach ($remarks as $remark) {
            $stat->data[1][] = [$remark->created_month, $remark->count_created_month];
        }
        $stat->prepare_html();
        $stat->prepare_script();

        return $stat;
    }

    /**
     * Templom karbantartók statisztikái.
     */
    protected function getChurchHolderStats(): array
    {
        $s4 = ['data' => [], 'labels' => []];

        $data = ChurchHolder::select('user_id', DB::raw('count(*) as count'))->groupBy('user_id')->orderBy('count')->get();

        $tmp = [];
        foreach ($data as $uid => $count) {
            if (isset($tmp[$count->count])) {
                ++$tmp[$count->count];
            } else {
                $tmp[$count->count] = 1;
            }
        }
        if (\count($tmp) > 0) {
            foreach ($tmp as $k => $v) {
                $s4['data'][] = [$k, $v];
            }
        }

        return $s4;
    }

    /**
     * Templomok ahol van Accessibility adat.
     */
    protected function getChurchAccessibility(): array
    {
        $accessibilityOSMTags = ['wheelchair', 'wheelchair:description', 'toilets:wheelchair', 'hearing_loop', 'disabled:description'];
        $results = DB::table('osmtags')
            ->select('osmtags.*', 'templomok.id as church_id')
            ->join('templomok', function ($join) {
                $join->on('templomok.osmid', '=', 'osmtags.osmid');
                $join->on('templomok.osmtype', '=', 'osmtags.osmtype');
            })
            ->whereNotNull('templomok.id')
            ->whereIn('osmtags.name', $accessibilityOSMTags)
            ->where('osmtags.value', '<>', '')
            ->get();

        $accessibility['churches'] = [];
        foreach ($accessibilityOSMTags as $tag) {
            $accessibility['tags'][$tag] = [];
        }
        foreach ($results as $res) {
            $accessibility['churches'][$res->church_id] = $res->church_id;
            if (isset($accessibility['tags'][$res->name][$res->value])) {
                ++$accessibility['tags'][$res->name][$res->value];
            } else {
                $accessibility['tags'][$res->name][$res->value] = 1;
            }
        }

        return $accessibility;
    }

    /**
     * Felhasználók regisztráltása és utolsó ténykedése.
     */
    protected function getUserStats(): array
    {
        $stat = [
            'labels' => ['utoljára aktív felhsználók', 'újonnan regisztrált (és aktivizált) felhasználók'],
            'data' => [0 => [], 1 => []],
        ];

        $results = DB::table('user')
            ->select(DB::raw('COUNT(*) as count'), DB::raw("date_format(lastactive,'%Y') as lastactive_year"))
            ->groupBy('lastactive_year')
            ->get();
        foreach ($results as $result) {
            if ($result->lastactive_year == 0) {
                $stat['early'] = $result->count;
            } else {
                $stat['data'][0][] = [(int) $result->lastactive_year, (int) $result->count];
            }
        }
        $results = DB::table('user')
            ->select(DB::raw('COUNT(*) as count'), DB::raw("date_format(regdatum,'%Y') as regdatum_year"))
            ->where('regdatum', '<>', '0000-00-00 00:00:00')
            ->where('regdatum', '>', '2015-00-00 00:00:00')
            ->where('lastlogin', '<>', '0000-00-00 00:00:00')
            ->groupBy('regdatum_year')
            ->get();
        foreach ($results as $result) {
            $stat['data'][1][] = [(int) $result->regdatum_year, (int) $result->count];
        }

        return $stat;
    }

    /**
     * ExternalApi Stats.
     */
    protected function getApiStat(): array
    {
        $s5 = ['data' => [], 'labels' => []];
        $data = DB::table('stats_externalapi')->select('name', 'date', DB::raw('SUM(count) count'), DB::raw('CONCAT(name,date)  namedate'))->where('date', '>', date('Y-m-d', strtotime('-1 month')))->groupBy('namedate')->orderBy('date', 'asc')->get();
        $data = collect($data)->groupBy(['name'])->toArray(); // ->transform(function($item, $k) {return $item->groupBy('name');})->toArray();
        $c = 0;
        foreach ($data as $apiname => $api) {
            $s5['labels'][$c] = $apiname;
            $s5['data'][$c] = [];
            foreach ($api as $date) {
                $s5['data'][$c][] = [$date->date, (int) $date->count];
            }
            ++$c;
        }

        return $s5;
    }

    /**
     * Magyarországi aktív templomok frissítettségének statisztikája.
     */
    protected function getActiveHungarianChurches()
    {
        $stat = DB::table('templomok')
            ->selectRaw('DATEDIFF(NOW(), frissites) DIV 365 as yearago')
            ->selectRAW('count(*) as count');
        $stat->where('orszag', 12)->where('ok', 'i')->where('miseaktiv', 1);
        foreach (['%isézőhely%', '%ápolna', '%özösségi%', '%imaház%', '%imaterem%'] as $notlike) {
            $stat->where('nev', 'not like', $notlike);
        }
        $stat->orderBy('frissites', 'DESC');

        $results = $stat->groupBy('yearago')->get();
        $sum = 0;
        $maxYear = 0;

        if ($results[0]->yearago > 0) {
            array_unshift($results, new \stdClass());
            $results[0]->yearago = 0;
            $results[0]->count = 0;
        }

        foreach ($results as $result) {
            $sum += $result->count;
            if ($maxYear < $result->yearago) {
                $maxYear = $result->yearago;
            }
        }

        $minColor = [255, 0, 0];
        $maxColor = [139, 42, 42];
        foreach ($results as $k => $result) {
            $results[$k]->percent = round($result->count / ($sum / 100));

            $r = round($minColor[0] + (($maxColor[0] - $minColor[0]) / ($maxYear - 2) * ($result->yearago - 2)));
            $g = round($minColor[1] + (($maxColor[1] - $minColor[1]) / ($maxYear - 2) * ($result->yearago - 2)));
            $b = round($minColor[2] + (($maxColor[2] - $minColor[2]) / ($maxYear - 2) * ($result->yearago - 2)));

            if ($result->yearago == 0) {
                $results[$k]->rgb = [0, 255, 0];
            } elseif ($result->yearago == 1) {
                $results[$k]->rgb = [255, 255, 0];
            } else {
                $results[$k]->rgb = [$r, $g, $b];
            }
        }

        return $results;
    }

    protected function getOsmTags(): \Illuminate\Support\Collection
    {
        $osmtags = DB::table('osmtags')
            ->select([
                DB::raw('count(*) as count'),
                DB::raw('count(distinct osmtags.value) as dist'),
                DB::raw('templomok.id as church_id'),
                'osmtags.*',
            ])
            ->join('templomok', function ($join) {
                $join->on('templomok.osmid', '=', 'osmtags.osmid');
                $join->on('templomok.osmtype', '>=', 'osmtags.osmtype');
            })
            ->where('value', '<>', '')
            ->whereNotNull('templomok.id')
            ->groupBy('osmtags.name')
            ->orderBy('count', 'desc')
            ->orderBy('dist', 'desc')
            ->orderBy('name')
            ->get();

        return $osmtags;
    }
}
