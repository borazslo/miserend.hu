<?php
namespace Html\Calendar;

use Carbon\Carbon;
use ExternalApi\ElasticsearchApi;
use Eloquent\CalGeneratedPeriod;
use Eloquent\CalMass;
use Eloquent\CalPeriod;

if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

class Generate extends \Html\Calendar\CalendarApi {

    protected $elastic;

    public function __construct($path = false) {

        if($_SERVER['REQUEST_METHOD'] === false ) {
            return;            
        }   

        $this->elastic = new ElasticsearchApi();        
        if (!$this->elastic->isexistsIndex('mass_index')) {
            $this->createMassIndex();            
        }

        $this->tids = !empty($_GET['tids']) ? (is_array($_GET['tids']) ? $_GET['tids'] : [$_GET['tids']]) : [];
        if (empty($this->tids)) {
            $this->sendJsonError('Nincs templom ID megadva.', 400);
            exit;
        }

        $this->years = !empty($_GET['years']) ? (is_array($_GET['years']) ? $_GET['years'] : [$_GET['years']]) : [];
        if (empty($this->years)) {
            $this->sendJsonError('Nincs év megadva.', 400);
            exit;
        }

        

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
                http_response_code(200);
                exit();

            case 'GET':  
                  // Itt egy keresés volt korábban, de úgy tűnt semmi nem használja. 
                  break;
                              
            case 'PUT':
                $years = is_array($this->years) ? $this->years : [$this->years];
                
                $debug = \ExternalApi\ElasticsearchApi::updateMasses($years, $this->tids);

                echo json_encode([
                    'success' => true,
                    'debug'   => array_merge($debug, $this->debugLog)
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                break;
        }}

    private array $debugLog = [];

    private function logDebug(string $msg, array $ctx = []): void {
        $line = $msg;
        if (!empty($ctx)) {
            $line .= " | " . json_encode($ctx, JSON_UNESCAPED_UNICODE);
        }
        $this->debugLog[] = $line;
    }


    private function sendJsonError($message, $code): void {
        http_response_code($code);
        echo json_encode([
            'error' => true,
            'message' => $message,
            'code' => $code,
        ]);
    }

    private function convertRangeToUtc(?int $min, ?int $max, Carbon $date): array
    {
        $range = [];

        if ($min !== null) {
            $local = $date->copy()->setTime(floor($min / 60), $min % 60);
            $utc   = $local->copy()->setTimezone('UTC');
            $range['gte'] = $utc->hour * 60 + $utc->minute;
        }

        if ($max !== null) {
            $local = $date->copy()->setTime(floor($max / 60), $max % 60);
            $utc   = $local->copy()->setTimezone('UTC');
            $range['lte'] = $utc->hour * 60 + $utc->minute;
        }

        return $range;
    }

  
    


    


    /**
     * Létrehozza a mass_index indexet az Elasticsearch-ben.
     * Ez a metódus a mass.json és church.json fájlokat használja a mapping és settings beállításokhoz.
     * @throws \Exception
     */
    public function createMassIndex(): void
    {
        $massFilePath = '../docker/elasticsearch/mappings/mass.json';
        if (!file_exists($massFilePath)) {
            throw new \Exception("File not found: " . $massFilePath);
        }
        $massData = file_get_contents($massFilePath);
        $churchFilePath = '../docker/elasticsearch/mappings/church.json';
        if (!file_exists($churchFilePath)) {
            throw new \Exception("File not found: " . $churchFilePath);
        }
        $churchData = file_get_contents($churchFilePath);
        $data = json_decode($massData, true);
        $data['settings'] = json_decode($churchData, true)['settings'];
        $data['mappings']['properties']['church'] = json_decode($churchData, true)['mappings'];

        if (!$this->elastic->putIndex('mass_index', $data)) {				
            throw new \Exception("Failed to create index: mass_index");
        }                
    }







}
