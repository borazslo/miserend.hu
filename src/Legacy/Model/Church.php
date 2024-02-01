<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Model;

use App\Legacy\Parish;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 ALTER TABLE `miserend`.`templomok`
 ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;
 */

/**
 * @method inBBox
 */
class Church extends Model
{
    use SoftDeletes;

    protected $table = 'templomok';
    protected $appends = ['fullName', 'location', 'links'];

    public function photos()
    {
        return $this->hasMany(Photo::class)->ordered();
    }

    public function keywordshortcuts()
    {
        return $this->hasMany(KeywordShortcut::class);
    }

    public function remarks()
    {
        return $this->hasMany(Remark::class)->orderBy('created_at', 'DESC');
    }

    public function updateNeighbours()
    {
        // TODO: Does not work!
        // "Call to undefined method Illuminate\Database\Query\Builder::MupdateChurch()"
        $distance = new Distance();
        $distance->MupdateChurch($this);
    }

    public function neighbours()
    {
        return $this->where('templomok.id', $this->id)
                ->join('distances', function ($join) {
                    $join->on('distances.fromLon', '=', 'lon');
                    $join->on('distances.fromLat', '=', 'lat');
                })
                  ->join('templomok as churchTo', function ($join) {
                      $join->on('distances.toLon', '=', 'churchTo.lon');
                      $join->on('distances.toLat', '=', 'churchTo.lat');
                  })
                ->select('distances.*', 'churchTo.*')
                ->where('churchTo.ok', 'i')
                ->orderBy('distances.distance', 'ASC');
    }

    public function neighbourss()
    {
        return self::join('distances', function ($join) {
            $join->on('distances.toLon', '=', 'lon');
            $join->on('distances.toLat', '=', 'lat');
        })
                    ->where('distances.fromLon', $this->lon)
                    ->where('distances.fromLat', $this->lat)
                    ->where('ok', 'i')
                            ->select('templomok.*', 'distances.distance')
                    ->orderBy('distances.distance', 'ASC');
    }

    public function getNeighboursAttribute()
    {
        return $this->neighbourss()
                    ->limit(10)
                    ->get();
    }

    /*
     * scopes
     *  boundaries()
     *  inBBox()
     *  churchesAndMore
     *  countByUpdatedMonth
     *  countByUpdatedYear
     *  selectUpdatedMonth- ?
     *  selectUpdatedYear
     *  whereShortcutLike($keyword, $type)
     */
    public function scopeBoundaries($query)
    {
        return $query->belongsToMany(Boundary::class, 'lookup_boundary_church')
                ->withTimestamps();
    }

    public function scopeInBBox($query, $bbox)
    {
        return $query->whereBetween('lat', [$bbox['latMin'], $bbox['latMax']])
                            ->whereBetween('lon', [$bbox['lonMin'], $bbox['lonMax']]);
    }

    public function scopeChurchesAndMore($query)
    {
        return $query->where('nev', 'NOT LIKE', '%kápolna%');
    }

    public function scopeSelectUpdatedMonth($query)
    {
        return $query->addSelect(DB::raw('DATE_FORMAT(frissites,\'%Y-%m\') as updated_month'), DB::raw('COUNT(*) as count_updated_month'));
    }

    public function scopeSelectUpdatedYear($query)
    {
        return $query->addSelect(DB::raw('DATE_FORMAT(frissites,\'%Y\') as updated_year'), DB::raw('COUNT(*) as count_updated_year'));
    }

    public function scopeCountByUpdatedMonth($query)
    {
        return $query->selectUpdatedMonth()
                        ->groupBy('updated_month')->orderBy('updated_month');
    }

    public function scopeCountByUpdatedYear($query)
    {
        return $query->selectUpdatedYear()
                        ->groupBy('updated_year')->orderBy('updated_year');
    }

    public function scopeWhereShortcutLike($query, $keyword, $type)
    {
        return $query->whereHas('keywordshortcuts', function ($query) use ($keyword, $type) {
            $query->where('type', $type)->where('value', 'like', $keyword);
        });
    }

    /*
     * getSomethingAttribute -> $this->something;
     *
     * liturgiatv
     * denomination
     * holders
     * links
     * readAcess (of current user)
     * writeAccess (of current user)
     * jelzes
     * fullName
     * remarksSatus
     * location
     * osm
     * kozossegek
     */
    public function getLiturgiatvAttribute($value)
    {
        $litapi = new \App\Legacy\Services\ExternalApi\LiturgiatvApi();
        $datas = $litapi->getByChurch($this->id);
        foreach ($datas as $key => $data) {
            if (!isset($data->duration)) {
                $data->duration = 60;
            }

            // https://github.com/molnarm/zsolozsma#API
            $minutesToStart = (strtotime($data->date.' '.$data->time) - time()) / 60;
            if ($minutesToStart > 15) {
                $datas[$key]->state = 1;
                $datas[$key]->iconstate = 'disabled';
            } elseif ($minutesToStart > 0) {
                $datas[$key]->state = 2;
                $datas[$key]->iconstate = 'higlight';
            } elseif ($minutesToStart > -15) {
                $datas[$key]->state = 3;
                $datas[$key]->iconstate = 'live';
            } elseif ($minutesToStart > 0 - $data->duration - 15) {
                $datas[$key]->state = 4;
                $datas[$key]->iconstate = 'higlight';
            } else {
                unset($datas[$key]);
            }
        }

        return $datas;
    }

    public function getDenominationAttribute($value)
    {
        return \in_array($this->egyhazmegye, [34, 17, 18]) ? 'greek_catholic' : 'roman_catholic';
    }

    public function getHoldersAttribute($value)
    {
        $holders = ChurchHolder::where('church_id', $this->id)->orderBy('status')->orderBy('updated_at', 'desc')->get()->groupBy('status');

        return $holders;
    }

    public function getLinksAttribute($value)
    {
        $links = $this->hasMany(ChurchLink::class)->get();

        return $links;
    }

    public function getReadAccessAttribute($value)
    {
        $user = $this->getSecurity()->getUser();

        return $this->checkReadAccess($user);
    }

    public function getWriteAccessAttribute($value)
    {
        $user = $this->getSecurity()->getUser();

        return $this->checkWriteAccess($user);
    }

    public function getJelzesAttribute()
    {
        $jelzes = ''; // $this->remarksStatus['html'];

        if (1 == $this->miseaktiv) {
            $countMasses = DB::table('misek')->where('tid', $this->id)->where('torles', '0000-00-00 00:00:00')->count();
            if ($countMasses < 1) {
                $jelzes .= ' <i class="fa fa-lightbulb-o fa-lg" title="Nincs hozzá mise!" style="color:#FDEE00"></i> ';
            }
        }

        if ('n' == $this->ok) {
            $jelzes .= " <i class='fa fa-ban fa-lg' title='Nem engedélyezett!' style='color:red'></i> ";
        } elseif ('f' == $this->ok) {
            $jelzes .= " <img src=/img/ora.gif title='Feltöltött/módosított templom, áttekintésre vár!' align=absmiddle> ";
        }

        if ('i' == $this->ok && 1 == $this->miseaktiv) {
            $updatedTime = strtotime($this->frissites);
            if ($updatedTime < strtotime('-10 years')) {
                $jelzes .= " <i class='fa fa-exclamation-triangle fa-lg' title='Több mint 10 éves adatok!' style='color:red'></i> ";
            } elseif ($updatedTime < strtotime('-5 year')) {
                $jelzes .= " <i class='fa fa-exclamation fa-lg' title='Több mint öt éves adatok!' style='color:red'></i> ";
            }
        }
        if ($this->lat <= 0 || $this->lon <= 0) {
            $jelzes .= '<span class="glyphicon glyphicon glyphicon-map-marker" aria-hidden="true" style="color:red" title="Nincsen koordináta!"></span>';
        }
        if ('' == $this->osmid || '' == $this->osmtype) {
            $jelzes .= '<span class="glyphicon glyphicon glyphicon-map-marker" aria-hidden="true" style="color:grey" title="OSM adat hiányzik még"></span>';
        }

        return $jelzes;
    }

    public function getRemarksiconAttribute()
    {
        $allapotok = Remark::where('church_id', $this->id)->groupBy('allapot')->pluck('allapot')->toArray();
        if (\in_array('u', $allapotok)) {
            $remarksicon = 'ICONS_REMARKS_NEW';
        } elseif (\in_array('f', $allapotok)) {
            $remarksicon = 'ICONS_REMARKS_PROCESSING';
        } elseif (\count($allapotok) > 0) {
            $remarksicon = 'ICONS_REMARKS_ALLDONE';
        } else {
            $remarksicon = 'ICONS_REMARKS_NO';
        }

        return $remarksicon;
    }

    public function getFullNameAttribute($value)
    {
        $return = $this->nev;
        if (!empty($this->ismertnev)) {
            $return .= ' ('.$this->ismertnev.')';
        } else {
            $return .= ' ('.$this->varos.')';
        }

        return $return;
    }

    public function getRemarksStatusAttribute($value)
    {
        $return = false;
        $remark = $this->remarks()
                        ->select('allapot')
                        ->groupBy('allapot')
                        ->orderByRaw("FIND_IN_SET(allapot, 'u,f,j')")->first();

        if (!$remark) {
            $return['text'] = 'Nincsenek észrevételek';
            $return['html'] = "<i class='fa fa-gift fa-lg' style='color:#D3D3D3'  title='".$return['text']."'></i>";
            $return['mark'] = false;
        } elseif ('u' == $remark->allapot) {
            $return['text'] = 'Új észrevételt írtak hozzá!';
            $return['html'] = "<a href=\"javascript:OpenScrollWindow('/templom/$this->id/eszrevetelek',550,500);\"><img src=/img/csomag.gif title='".$return['text']."' align=absmiddle border=0></a> ";
            $return['mark'] = 'u';
        } elseif ('f' == $remark->allapot) {
            $return['text'] = 'Észrevétel javítása folyamatban!';
            $return['html'] = "<a href=\"javascript:OpenScrollWindow('/templom/$this->id/eszrevetelek',550,500);\"><img src=/img/csomagf.gif title='".$return['text']."' align=absmiddle border=0></a> ";
            $return['mark'] = 'f';
        } elseif ('j' == $remark->allapot) {
            $return['text'] = 'Észrevételek';
            $return['html'] = "<a href=\"javascript:OpenScrollWindow('/templom/$this->id/eszrevetelek',550,500);\"><img src=/img/csomag1.gif title='".$return['text']."' align=absmiddle border=0></a> ";
            $return['mark'] = 'j';
        }

        return $return;
    }

    public function getLocationAttribute($value)
    {
        $location = new \stdClass();

        $location->lat = $this->lat;
        $location->lon = $this->lon;
        if ($this->osmtype && $this->osmid) {
            $location->osm = [
                'type' => $this->osmtype,
                'id' => $this->osmid,
                'url' => 'https://www.openstreetmap.org/'.$this->osmtype.'/'.$this->osmid,
                 ];
        } else {
            $location->address = $this->cim;
        }
        if ('' != $this->megkozelites) {
            $location->access = $this->megkozelites;
        }

        /* Address addr:steet, addr:housenumber */
        $tags = collect(
            OSMTag::where('osmtype', $this->osmtype)
                ->where('osmid', $this->osmid)
                ->whereIn('name', ['addr:street', 'addr:housenumber'])
                ->orderBy('name', 'DESC')
                ->get())->keyBy('name');
        if (\count($tags) > 0) {
            $location->address = '';
            foreach ($tags as $tag) {
                $location->address .= $tag->value.' ';
            }
        }

        /* Adminisrative Boundaries(Country,County, City, District) */
        $boundaries = $this->boundaries()
                ->where('boundary', 'administrative')
                ->whereIn('admin_level', [2, 6, 8, 9, 10])
                ->orderBy('admin_level')
                ->get()->toArray();

        if (\array_key_exists(0, $boundaries)) {
            $location->country = $boundaries[0];
        }
        if (\array_key_exists(1, $boundaries)) {
            $location->county = $boundaries[1];
        }
        if (\array_key_exists(2, $boundaries)) {
            $location->city = $boundaries[2];
        }
        if (\array_key_exists(3, $boundaries)) {
            $location->district = $boundaries[3];
        }

        return $location;
    }

    public function getOsmAttribute($value)
    {
        if (false == $this->osmid || false == $this->osmtype) {
            return false;
        }

        $osm = OSM::where('osmtype', $this->osmtype)
                ->where('osmid', $this->osmid)
                ->first();
        if (!$osm) {
            $osm = new OSM(['osmid' => $this->osmid, 'osmtype' => $this->osmtype]);
        }

        return $osm;
    }

    public function getKozossegekAttribute($value)
    {
        $api = new \App\Legacy\Services\ExternalApi\KozossegekApi();
        $api->query = 'miserend/'.$this->id;
        $api->run();
        if (isset($api->jsonData->data) > 0) {
            return $api->jsonData->data;
        } else {
            return false;
        }
    }

    /*
     * What does 'M' mean?
     */
    public function MgetReligious_administration()
    {
        $this->religious_administration = new \stdClass();
        $this->religious_administration->diocese = new \App\Legacy\Diocese();
        $this->religious_administration->diocese->getByChurchId($this->id);
        $this->religious_administration->deaconry = new \App\Legacy\Deaconry();
        $this->religious_administration->deaconry->getByChurchId($this->id);
        $this->MgetParish();
    }

    public function MgetParish()
    {
        if (!isset($this->religious_administration)) {
            $this->religious_administration = new \stdClass();
        }
        $parish = new Parish();
        $parish->getByChurchId($this->id);
        $this->religious_administration->parish = $parish;
    }

    public function checkReadAccess($_user)
    {
        $access = false;
        if ('i' == $this->ok) {
            $access = true;
        }

        if ($this->checkWriteAccess($_user)) {
            $access = true;
        }

        $user = $this->getSecurity()->getUser();
        if ($user->getUid() == $_user->getUid()) {
            $this->readAcess = $access;
        }

        return $access;
    }

    public function checkWriteAccess($_user)
    {
        $access = false;

        if ($_user->checkRole('miserend')) {
            $access = true;
        }

        if (ChurchHolder::where('church_id', $this->id)->where('user_id', $_user->getUid())->where('status', 'allowed')->first()) {
            $access = true;
        }

        if (DB::table('egyhazmegye')->where('id', $this->egyhazmegye)->where('felelos', $_user->getUsername())->first()) {
            $access = true;
        }

        $user = $this->getSecurity()->getUser();
        if ($user->getUid() == $_user->getUid()) {
            $this->writeAcess = $access;
        }

        return $access;
    }

    public function MgetDioceseId()
    {
        if ($this->religious_administratin) {
            return $this->religious_administration->diocese->id;
        } else {
            return false;
        }
    }

    public function boundaries()
    {
        return $this->belongsToMany(Boundary::class, 'lookup_boundary_church')
                ->withTimestamps();
    }

    /*
     * A régi templomok.egyhazmegye/espereskerulet/orszag/megye/varos -ból csinál
     * boundary értéket, ha még nincs. Ill. összekapcsolást.
     */
    public function MmigrateBoundaries()
    {
        global $_egyhazmegyek, $_espereskeruletek, $_orszagok, $_megyek, $_varosok;

        /* egyházmegye */
        $tmp = $this->boundaries()
                ->where('boundary', 'religious_administration')
                ->where('denomination', 'LIKE', '%_catholic')
                ->where('admin_level', 6)
                ->get()->toArray();
        if ([] == $tmp) {
            $boundary = Boundary::firstOrNew(['boundary' => 'religious_administration', 'denomination' => $this->denomination, 'admin_level' => 6, 'name' => $_egyhazmegyek[$this->egyhazmegye]->nev]);
            $boundary->save();
            $this->boundaries()->attach($boundary->id);
        }

        /* espereskerület */
        $tmp = $this->boundaries()
                ->where('boundary', 'religious_administration')
                ->where('denomination', 'LIKE', '%_catholic')
                ->where('admin_level', 7)
                ->get()->toArray();
        if ([] == $tmp) {
            $boundary = Boundary::firstOrNew(['boundary' => 'religious_administration', 'denomination' => $this->denomination, 'admin_level' => 7, 'name' => $_espereskeruletek[$this->espereskerulet]->nev]);
            $boundary->save();
            $this->boundaries()->attach($boundary->id);
        }

        /* ország */
        $tmp = $this->boundaries()
                ->where('boundary', 'administrative')
                ->where('admin_level', 2)
                ->get()->toArray();
        if ([] == $tmp) {
            $boundary = Boundary::firstOrNew(['boundary' => 'administrative', 'admin_level' => 2, 'name' => $_orszagok[$this->orszag]->nev]);
            $boundary->save();
            $this->boundaries()->attach($boundary->id);
        }

        /* megye */
        $tmp = $this->boundaries()
                ->where('boundary', 'administrative')
                ->where('admin_level', 6)
                ->get()->toArray();
        if ([] == $tmp) {
            if (isset($_megyek[$this->megye])) {
                $boundary = Boundary::firstOrNew(['boundary' => 'administrative', 'admin_level' => 6, 'name' => $_megyek[$this->megye]->nev.' megye']);
                $boundary->save();
                $this->boundaries()->attach($boundary->id);
            }
        }

        /* város */
        $tmp = $this->boundaries()
                ->where('boundary', 'administrative')
                ->where('admin_level', 8)
                ->get()->toArray();
        if ([] == $tmp) {
            $boundary = Boundary::firstOrNew(['boundary' => 'administrative', 'admin_level' => 8, 'name' => $this->varos]);
            $boundary->save();
            $this->boundaries()->attach($boundary->id);
        }
    }

    public function delete()
    {
        // $this->neighbours()->delete();
        // Distance::where('church_to', $this->id)->delete(); fromLat, fromLon
        // Distance::where('church_from', $this->id)->delete(); toLat, toLon

        ChurchHolder::where('church_id', $this->id)->delete();
        Favorite::where('tid', $this->id)->delete();
        ChurchLink::where('church_id', $this->id)->delete();

        // Nem elegáns:
        DB::table('lookup_boundary_church')->where('church_id', $this->id)->delete();
        DB::table('lookup_church_osm')->where('church_id', $this->id)->delete();

        DB::table('misek')->where('tid', $this->tid)->delete();

        $this->remarks()->delete();
        $this->photos()->delete();

        parent::delete();
    }
}
