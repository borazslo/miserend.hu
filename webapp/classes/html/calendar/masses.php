<?php
namespace Html\Calendar;

use ExternalApi\ElasticsearchApi;
use Eloquent\CalMass;
use Eloquent\CalModel;
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

                if (!$this->church->writeAccess) {
                    $this->sendJsonError('Hiányzó jogosultság!', 403);
                    exit;
                }

                $input = json_decode(file_get_contents('php://input'), true);
                $changeRequest = new ChangeRequest($input['masses'], $input['deletedMasses']);
                $this->save($changeRequest);
                $this->optimizeExperiods();
                // Ha frissítettünk egy miserendet, akkor mindig és automatikusan a dátuma is legyen friss!                
                $this->church->frissites = date('Y-m-d');
                $this->church->save();
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

    // Az experiod azaz kizárt időszak azonosítók között 
    // időnként maradhat olyan, amilyen időszak már nincs is, ezért nem kéne kizárni
    // Ezeket lapátoljuk el az útból
    private function optimizeExperiods() {

        // Build list of currently used period_ids for this church
        $periodIds = CalMass::where('church_id', $this->tid)
            ->get()
            ->pluck('period_id')
            ->filter()    // remove null/empty
            ->unique()    // keep only unique ids
            ->values()    // reindex the collection
            ->toArray();

        // Find masses that have experiod set
        $experiods = CalMass::where('church_id', $this->tid)
            ->whereNotNull('experiod')
            ->groupBy('experiod')
            ->get();
                            
        foreach($experiods as $current) {            
            $cleanedExperiods = [];
            $toChange = CalMass::where('church_id', $this->tid);
            foreach($current->experiod as $k => $experiodId) {
                $toChange = $toChange->whereJsonContains('experiod',$experiodId);                
                if(in_array($experiodId, $periodIds)) {
                    $cleanedExperiods[] = $experiodId;
                }
            }
            $cleanedExperiodString = !empty($cleanedExperiods) ? json_encode($cleanedExperiods) : null;
        
            $toChange = $toChange->whereRaw('JSON_LENGTH(experiod) = ?', [count($current->experiod)]);
                
            //printr($toChange->get()->toArray());
            if($cleanedExperiodString === $current->experiod) {
                // no change
                continue;
            }
            $toChange->update(['experiod' => $cleanedExperiodString]);
        }

        return true;
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
