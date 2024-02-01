<?php

namespace App\Legacy\Services;

use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(autowire: true)]
class ConstantsProvider
{
    public const ROLES = ['miserend', 'user'];

    public function __construct(private readonly DB $database)
    {
    }

    public static function getMonths(): array
    {
        return [
            1 => ['jan', 'január'],
            2 => ['feb', 'február'],
            3 => ['márc', 'március'],
            4 => ['ápr', 'április'],
            5 => ['máj', 'május'],
            6 => ['jún', 'június'],
            7 => ['júl', 'július'],
            8 => ['aug', 'augusztus'],
            9 => ['szept', 'szeptember'],
            10 => ['okt', 'október'],
            11 => ['nov', 'november'],
            12 => ['dec', 'december'],
        ];
    }

    private iterable $egyhazmegyek;

    public function getEgyhazmegyek(): iterable
    {
        if (!isset($this->egyhazmegyek)) {
            $this->egyhazmegyek = collect(DB::table('egyhazmegye')->get())->keyBy('id')->sortBy('sorrend');
        }

        return $this->egyhazmegyek;
    }

    private iterable $espereskeruletek;

    public function getEspereskerulet(): iterable
    {
        if (!isset($this->espereskeruletek)) {
            $this->espereskeruletek = collect(DB::table('espereskerulet')->get())->keyBy('id');
        }

        return $this->espereskeruletek;
    }

    private iterable $orszagok;

    public function getCountries(): iterable
    {
        if (!isset($this->orszagok)) {
            $this->orszagok = collect(DB::table('orszagok')->get())->keyBy('id');
        }

        return $this->orszagok;
    }

    private iterable $megyek;

    public function getCounties(): iterable
    {
        if (!isset($this->megyek)) {
            $this->megyek = collect(DB::table('megye')->select('*', 'megyenev as nev')->get())->keyBy('id');
        }

        return $this->megyek;
    }

    private iterable $varosok;

    public function getCities(): iterable
    {
        if (!isset($this->varosok)) {
            $this->varosok = collect(DB::table('varosok')->get())->keyBy('id');
        }

        return $this->varosok;
    }

    public function getPeriods(): array
    {
        return [
            0 => [
                'abbrev' => 0,
                'name' => '',
                'description' => 'minden',
            ],
            1 => [
                'abbrev' => 1,
                'name' => '1.',
            ],
            2 => [
                'abbrev' => 2,
                'name' => '2.',
            ],
            3 => [
                'abbrev' => 3,
                'name' => '3.',
            ],
            4 => [
                'abbrev' => 4,
                'name' => '4.',
            ],
            5 => [
                'abbrev' => 5,
                'name' => '5.',
            ],
            '-1' => [
                'abbrev' => '-1',
                'name' => 'utolsó',
            ],
            'ps' => [
                'abbrev' => 'ps',
                'name' => 'páros',
            ],
            'pt' => [
                'abbrev' => 'pt',
                'name' => 'páratlan',
            ],
        ];
    }

    public function getLanguages(): array
    {
        $nyelv = [
            'h' => 'magyar',
            'en' => 'angol',
            'fr' => 'francia',
            'gr' => 'görög',
            'hr' => 'horvát',
            'va' => 'latin',
            'pl' => 'lengyel',
            'de' => 'német',
            'it' => 'olasz',
            'ro' => 'román',
            'es' => 'spanyol',
            'sk' => 'szlovák',
            'si' => 'szlovén',
            'uk' => 'ukrán',
        ];
        foreach ($nyelv as $k => $v) {
            $nyelv[$k] = [
                'abbrev' => $k,
                'name' => $v,
                'file' => 'zaszloikon/'.$k.'.gif',
                'description' => $v.' nyelven',
            ];
        }
        return $nyelv;
    }

    public function getAttributes(): array
    {
        $milyen = [
            'csal' => [
                'abbrev' => 'csal',
                'name' => 'családos/mocorgós',
                'file' => 'lany.png',
                'group' => 'age',
            ],
            'd' => [
                'abbrev' => 'd',
                'name' => 'diák',
                'file' => 'diak.gif',
                'group' => 'age',
            ],
            'ifi' => [
                'abbrev' => 'ifi',
                'name' => 'ifjúsági/egyetemista',
                'file' => 'fiu.png',
                'group' => 'age',
            ],
            'g' => [
                'abbrev' => 'g',
                'name' => 'gitáros',
                'file' => 'gitar.gif',
                'group' => 'music',
            ],
            'cs' => [
                'abbrev' => 'cs',
                'name' => 'csendes',
                'file' => 'csendes.gif',
                'group' => 'music',
            ],
            'gor' => [
                'abbrev' => 'gor',
                'name' => 'görögkatolikus liturgia',
                'file' => 'jelzes1.png',
                'group' => 'liturgy',
                'isitmass' => true,
            ],
            'rom' => [
                'abbrev' => 'rom',
                'name' => 'római katolikus szentmise',
                'file' => 'jelzes10.png',
                'group' => 'liturgy',
                'isitmass' => true,
            ],
            'regi' => [
                'abbrev' => 'regi',
                'name' => 'régi rítusú szentmise',
                'file' => 'jelzes6.png',
                'group' => 'liturgy',
                'isitmass' => true,
            ],
            'ige' => [
                'abbrev' => 'ige',
                'name' => 'igeliturgia',
                'file' => 'biblia.gif',
                'group' => 'liturgy',
            ],
            'vecs' => [
                'abbrev' => 'vecs',
                'name' => 'vecsernye',
                'file' => 'jelzes7.png',
                'group' => 'liturgy',
            ],
            'utr' => [
                'abbrev' => 'utr',
                'name' => 'utrenye',
                'file' => 'jelzes8.png',
                'group' => 'liturgy',
            ],
            'szent' => [
                'abbrev' => 'szent',
                'name' => 'szentségimádás',
                'file' => 'jelzes9.png',
                'group' => 'liturgy',
            ],
        ];
        foreach ($milyen as $k => $v) {
            if (!isset($v['description'])) {
                $milyen[$k]['description'] = $v['name'];
            }
        }
        return $milyen;
    }
}
