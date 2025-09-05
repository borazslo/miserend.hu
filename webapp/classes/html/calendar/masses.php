<?php
namespace Html\Calendar;

use ExternalApi\ElasticsearchApi;
use Html\Calendar\Model\CalMass;
use Html\Calendar\Model\CalModel;
use Html\Calendar\Http\ChangeRequest;
use RRule\RRule;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

class Masses extends \Html\Calendar\CalendarApi {

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
            case 'POST':
                $this->church->append(['writeAccess']);

//                if (!$this->church->writeAccess) {
//                    $this->sendJsonError('Hiányzó jogosultság!', 403);
//                    exit;
//                }

                $input = json_decode(file_get_contents('php://input'), true);
                $changeRequest = new ChangeRequest($input['masses'], $input['deletedMasses']);
                $this->save($changeRequest);
                echo json_encode($this->getByChurchId($this->tid));
                break;

            default:
                $this->sendJsonError('Method not allowed', 405);
                exit;
        }
    }

    public function getByChurchId(int $churchId): array {
        return CalMass::where('church_id', $churchId)->get()->toArray();
    }

    public function save(ChangeRequest $changeRequest): void
    {
        // Törlendő misék
        if (!empty($changeRequest->deletedMasses)) {
            CalMass::whereIn('id', $changeRequest->deletedMasses)->delete();
        }

        // Új vagy frissítendő misék
        foreach ($changeRequest->masses as $mass) {
            $massData = is_array($mass) ? $mass : (array) $mass;
            $massData = CalModel::arrayKeysToSnakeCase($massData);

            // Ha negatív az ID, töröljük
            if (isset($massData['id']) && $massData['id'] < 0) {
                unset($massData['id']);
            }

            if (isset($massData['id']) && CalMass::find($massData['id'])) {
                // Frissítés
                CalMass::where('id', $massData['id'])->update($massData);
            } else {
                // Új rekord – automatikus ID generálás
                CalMass::create($massData);
            }
        }
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
