<?php
namespace html\calendar;

use ExternalApi\ElasticsearchApi;
use Html\Calendar\Model\CalMass;
use RRule\RRule;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

class Church extends \Html\Calendar\CalendarApi {

    protected $elastic;

    public function __construct($path) {

        if (empty($path[0])) {
            $this->sendJsonError('Hiányzó templom azonosító.', 400);
            exit;
        }

        $this->tid = $path[0];

        $this->church = \Eloquent\Church::find($this->tid);
        if (!$this->church) {
            $this->sendJsonError('Nincs ilyen templom.', 404);
            exit;
        }

        $this->elastic = new ElasticsearchApi();

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
                http_response_code(200);
                exit();
            case 'GET':
                $response = [
                    'id' => $this->tid,
                    'name' => $this->church->nev,
                    'rite' => strtoupper($this->church->denomination),
                    //TODO: HACK MOCK
                    'timeZone' => 'Europe/Budapest',
                    'masses' => $this->getByChurchId($this->tid)
                ];
                echo json_encode($response);
                break;
            default:
                $this->sendJsonError('Method not allowed', 405);
                exit;
        }
    }

    public function getByChurchId(int $churchId): array {
        return CalMass::where('church_id', $churchId)->get()->toArray();
    }

    private function sendJsonError($message, $code): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => $message,
            'code' => $code,
        ]);
    }
}
