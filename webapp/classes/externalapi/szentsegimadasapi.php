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
    
    function buildQuery() {
        global $config;
        $this->rawQuery = $this->query;        
    }
	
    function run() {
        $this->runQuery();
                 
        if (preg_match('/<ul class=\"talalatok\">(.*)<\/ul>/s', $this->rawData, $match)) {

            $c=0;
            $html = $match[1];
            unset($this->rawData);
            DB::table('szentsegimadasok')->truncate();
            set_time_limit(300); // 300 seconds
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
                        //echo $matchLi[2]."\n";
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
                     
                if($c > 200000000) break;
                $html = preg_replace('/<li>(.*?)<\/li>/s','', $html, 1);
            }

         
        }
        
        if(count($this->solrError['null']) > 0) {
            echo "Vigyázat! Nincs találat: \n";
            sort($this->solrError['null']);
            printr($this->solrError['null']);
        }
        if(count($this->solrError['multiple']) > 0) {
            echo "Vigyázat! Több találat van: \n";
            sort($this->solrError['multiple']);
            printr($this->solrError['multiple']);
        }
        

    }

    function findChurch($data) {
        $solr = new \ExternalApi\SolrApi();
        $solr->search($data['templom']." ".$data['varos']);
        if($solr->jsonData->response->numFound == 1) {
            return $solr->jsonData->response->docs[0]->id;
        } elseif($solr->jsonData->response->numFound > 1) {      
            $text = $data['templom']." ".$data['varos'] . " (-".$solr->jsonData->response->numFound.")";      
            if (!in_array($text, $this->solrError['multiple'])) {
                $this->solrError['multiple'][] = $text;
            }
            return $solr->jsonData->response->docs[0]->id;
        }   else {

            
            $textToSearch = preg_replace('/^(.*?)\(.*/','$1',$data['templom']);
            $array = [ 'téli', 'tél','nyár', '\(\)','nagyböjt', ' szo$', ' (v|p|szo|p\.)$', '(hétfő|péntek)', '( 3\.|1\.vas\.| 2\.vas\.|3\. hétfő|4\. hétfő)$', '24 órás (1|2)\.' ];
            foreach($array as $a) {
                $textToSearch = preg_replace('/'.$a.'/','',$textToSearch);
            }
            $array = [
                ['Szent Család templom \(Széchenyivárosi templom\).*$', 'Szentcsalád templom'],
                ['Szent Család templom.*$', 'Szentcsalád templom', 'kecskemét'],
                ['Szent Család templom.*$', 'Szentcsalád templom', 'Kecskemét'],
                ['Szent Vér Bazilika.*$', 'Szent Vér templom', 'Báta'],
                ['Városmajori Jézus Szíve templom.*$', 'Jézus Szíve-templom Városmajor'],
                ['Árpád-házi Szent Margit templom','Szent Margit Templom','Budapest XIII. kerület'],
                ['\(\(Szent József templom \(Jezsuita templom\)','Szent József-templom', 'Szeged'],

                
                

                ['Szent István-bazilika, Szent Jobb kápolna.*$', 'Szent István-bazilika'],
                ['Bazilika Minor','Szeplőtelen Fogantatás-templom','Sárospatak'],
                ['Magyarok Nagyasszonya templom', 'Magyarok Nagyasszonya bazilika', 'Márianosztra']
            ];
            foreach($array as $a) {
                if(isset($a[2]) && $a[2] == $data['varos']) {
                    $textToSearch = preg_replace('/'.$a[0].'/',$a[1],$textToSearch);
                } elseif (!isset($a[2])) {
                    $textToSearch = preg_replace('/'.$a[0].'/',$a[1],$textToSearch);
                }                
            }
            
            $solr->search($textToSearch.", ".$data['varos']);
            
            if($solr->jsonData->response->numFound == 1) {
                return $solr->jsonData->response->docs[0]->id;
            } elseif($solr->jsonData->response->numFound > 1) {
                $text = $textToSearch." ".$data['varos'] . " (-".$solr->jsonData->response->numFound.")";
                if (!in_array($text, $this->solrError['multiple'])) {
                    $this->solrError['multiple'][] = $text;
                }
                return $solr->jsonData->response->docs[0]->id;
            }   else {
                $text = $textToSearch.", ".$data['varos'];
                if (!in_array($text, $this->solrError['null'])) {
                    $this->solrError['null'][] = $text;
                }
                return false;
            }
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

