<?php

namespace ExternalApi;

use Illuminate\Database\Capsule\Manager as DB;


class szentsegimadasApi extends \ExternalApi\ExternalApi {

    public $name = 'szentsegimadas';
    public $apiUrl = "https://szentsegimadas.hu" ;    
	public $format = 'html';
	public $cache = "1 day"; //false or any time in strtotime() format
	public $testQuery = '';
    public $solrError = [ 'null' => [], 'multiple' => []];
    public $postfields = [
        'telepules' => '',
        'datum' => '',
        'tipus' => '0',
        'gomb' => 'Keresés'
    ];
    private $foundChurches = [];
    
    function buildQuery() {
        global $config;
        $this->rawQuery = $this->query;        
    }
	
    function run() {
        $this->runQuery();

        $this->error = [];
        $this->error['null'] = [];
        $this->error['multiple'] = [];  

        if (preg_match('/<ul class=\"talalatok\">(.*)<\/ul>/s', $this->rawData, $match)) {
            
            $c=0;
            $html = $match[1];
            unset($this->rawData);
            DB::table('szentsegimadasok')->truncate();
            set_time_limit(300); // 300 seconds
            $this->elastic = new \ExternalApi\ElasticsearchApi();
            while (preg_match('/<li>(.*?)<\/li>/s', $html, $match)) {
                
                
                if(preg_match('/<b>(.*?)<\/b> \((.*)\)<br>(<a.*<\/a>|) ((\d{4}\.\d{2}\.\d{2}\.) (\d{2}:\d{2})) - (| )((\d{4}\.\d{2}\.\d{2}\. |)(\d{2}:\d{2}))( |)(<img.*?title="(.*?)".*? \/>| )((<div id="info.*?<b>Információk:<\/b><br \/>(.*?)<div.*becsuk<\/a><\/div>)| )/s',$match[1],$matchLi)) {

                    $eventDate = strtotime(rtrim(str_replace('.', '-', $matchLi[5]), '-'));
                    if ($eventDate >= strtotime('today')) {                        
                        $data = [
                            'varos' => $matchLi[1],
                            'templom' => $matchLi[2],                            
                            'nap' => $matchLi[5],
                            'kezdes' => $matchLi[6],
                            'veg' => $matchLi[10],
                            'allapot' => $matchLi[13],
                            'info' => isset($matchLi[16]) ? $matchLi[16] : ''
                        ]; 
                        $data['church_id'] = $this->findChurch($data);
                        
                        if ( $data['church_id'] > 0 ) {                            
                            $this->saveData2Database($data);
                        }
                        $c++;


                    }
                } else {
                    echo "jaj\n";
                    echo $match[1]."\n";
                }
                     
                if($c > 20000) break;
                $html = preg_replace('/<li>(.*?)<\/li>/s','', $html, 1);
            }

         
        }
        
        if(count($this->error['null']) > 0) {
            echo "Vigyázat! Nincs találat: \n";
            sort($this->error['null']);
            printr($this->error['null']);
        }
        if(count($this->error['multiple']) > 0) {
            echo "Vigyázat! Több találat van: \n";
            sort($this->error['multiple']);
            printr($this->error['multiple']);
        }
        

    }

    /**
     * A központi tesztelőt nem tudjuk használni, mert ez fura html scraper. Ezért van egyedi teszelőnk.
     */
    function test() {
        try {
            $this->runQuery();
        } catch (\Throwable $th) {
            throw new \Exception("Could not run query!\n".$th->getMessage());
        }
        
        if (!preg_match('/<ul class=\"talalatok\">(.*)<\/ul>/s', $this->rawData, $match)) {
            throw new \Exception("A szentsegmiadasok.hu html forrásával gond van. Nincs <ul class=\"talalatok\"> elem a válaszban!");
        }
        // Felszabadítunk némi memóriát
        unset($this->rawData);

        // Van-e legalább egy találat.
        if(!preg_match('/<li>(.*?)<\/li>/s', $match[1], $match)) {
            throw new \Exception("A szentsegmiadasok.hu html forrásával gond van. Nincs <li> elem a válaszban!");
        }

        if(!preg_match('/<b>(.*?)<\/b> \((.*)\)<br>(<a.*<\/a>|) ((\d{4}\.\d{2}\.\d{2}\.) (\d{2}:\d{2})) - (| )((\d{4}\.\d{2}\.\d{2}\. |)(\d{2}:\d{2}))( |)(<img.*?title="(.*?)".*? \/>| )((<div id="info.*?<b>Információk:<\/b><br \/>(.*?)<div.*becsuk<\/a><\/div>)| )/s',$match[1],$matchLi)) {
            throw new \Exception("A szentsegmiadasok.hu html forrásával gond van. Van szentségimádás, de nem a megszokott formátumban.");
        }

        return true;
    }

    function findChurch($data) {
        $keyword = $data['templom'] . ", " . $data['varos'];

        // keyword tisztítása
        $keyword = str_ireplace(
            [
                'téli', 'nyári', 'nyár',
                '3. hétfő', '4. hétfő', 
                'virrasztás első fele', 'virrasztás második fele', 'p.'
            ],
            '',
            $keyword
        );
        $keyword = trim(preg_replace('/\s+/', ' ', $keyword));
        $keyword = preg_replace('/\s+,/', ',', $keyword); // szünetek eltávolítása a vesszők előtt
        $keyword = preg_replace('/,+/', ',', $keyword);   // dupla vesszők eltávolítása
        
        if(isset($this->foundChurches[$keyword])) {
            return $this->foundChurches[$keyword];
        }
        
        // 
        // Van, hogy elsőre mégsem a jó templomot dombja ki a kereső, akkor itt kap fix értéket
        // Van hogy nem találjuk meg sehogy sem, akkor vagy kap itt egy értéket vagy false-al kimerülünk
        $array = [
            'Ferences Mária Misszionárius nővérek temploma, Budapest XIV. kerület' => false,
            'Isteni Szeretet Közösség, Törökbálint' => false,
            'Kápolna, Bernecebaráti' => false,
            'Shalom Közösség Kápolnájában, Bakáts tér 13., Budapest VIII. kerület' => false,            
            'Kisboldogasszony templom szo, Szabadka' => false,
            'Kisboldogasszony templom, Szabadka' => false,                        
            'Szent István Király, Makranc (Szeps)' => false,            
            'Szent István-bazilika, Budapest V. kerület' => 37,
            'Szent István-bazilika, Szent Jobb kápolna, Budapest V. kerület' => 37,
            'Szent László templom (Pestszentlőrinc-Havannatelep plébániatemplom), Budapest XVIII. kerület' => 2499,
            'Szent Rókus templom, Novi Sad - Újvidék, Szerbia' => false,
            'Szentháromság templom, Jászárokszállás' => 1682,
            'Szentháromság templom, Patak' => 922,
            'Szentháromság templom, Szigetmonostor' => 2136,
            'Szeplőtelen Fogantatás és Szent István király-templom (Karmelitatemplom), Győr' => false,
            'Szeretetláng kápolna, Törökbálint' => false            
        ];
        if(isset($array[$keyword])) {
            if($array[$keyword] === false) {
                $this->error['null'][] = $keyword;
            } 
            $this->foundChurches[$keyword] = $array[$keyword];
            return $array[$keyword];
        }

        try {
            $elastic = $this->elastic; 
            // TODO: lehetne hogy csak azon templomok között keressünk amihez még nincs kiosztva szentségimádás
            $response = $elastic->search($keyword,["from" => 0,"size" => 2, "sort" => ["_score" => "desc"]], ['type' => 'church']);    

            // Ha pontosan egy találatunk van, akkor boldogok vagyunk.
            // Bár, vigyázat, lehet hogy csak nagyon gyenge találatunk van és azt jól elfogadtunk.
            if(count($response->hits) == 1) {
                foreach($response->hits as $hit) {
                    $this->foundChurches[$keyword] = $hit->_source->id;
                    return $hit->_source->id;		
                }	
            }

            // Ha semmilyen találatunk nincs, az nem jó.
            if(count($response->hits) == 0) {
                $text = $data['templom']." ".$data['varos'];                
                $this->error['null'][] = $text;
                $this->foundChurches[$keyword] = false;
                return false;
            } 
            
            // Ha több találatunk van, akkor tovább gondolkodunk.
            elseif(count($response->hits) > 1) {
                // Új logika: ha az első találat legalább 20%-kal jobb, mint a második, visszaadjuk az első ID-t
                $score0 = isset($response->hits[0]->_score) ? $response->hits[0]->_score : 0;
                $score1 = isset($response->hits[1]->_score) ? $response->hits[1]->_score : 0;
                // Itt lehet 10%-ot beállítani, és akkor a kiírt maradékot megnézzük egyesével
                if ($score1 > 0 && $score0 >= 1.001 * $score1) {
                    $this->foundChurches[$keyword] = $response->hits[0]->_source->id;
                    return $response->hits[0]->_source->id;
                } else {                    
                    $this->error['multiple'][] = $keyword;
                    $this->foundChurches[$keyword] = false;
                    return false;
                }
            }             

        } catch (\Throwable $th) {
            throw new \Exception("Could not search churches!\n".$th->getMessage());
        }
        
    }

    function saveData2Database($data) {

        
        DB::table('szentsegimadasok')->insert([
            'church_id' => $data['church_id'],
            'date' => $data['nap'],
            'starttime' => $data['kezdes'],
            'endtime' => $data['veg'],
            'type' => $data['allapot'],
            'info' => $data['info']
        ]);
        
        return true;

    }

    static function cron() {
        $api = new \ExternalApi\szentsegimadasApi();
        //$api->query = "/kereses";
        $api->run();
    }
    
}

