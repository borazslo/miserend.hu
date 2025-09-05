<?php
namespace html\calendar;

use Exception;
use Path;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

class search extends \Html\Calendar\SearchApi {

    public function __construct($path) {
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
                http_response_code(200);
                exit;

            case 'GET':
                $this->getData();
                break;

            default:
                $this->sendJsonError('Method not allowed', 405);
        }
    }

    private function getData(): void {

        $attributes = unserialize(ATTRIBUTES);
        $languages = unserialize(LANGUAGES);
        global $_egyhazmegyek, $_espereskeruletek, $_orszagok, $_megyek, $_varosok;


        echo json_encode([
            'attributes' => array_map(fn($a) => ['id' => $a['abbrev'], 'name' => $a['name'], 'group' => $a['group']], $attributes),
            'languages' => array_map(fn($l) => ['id' => $l['abbrev'], 'name' => $l['name']], $languages),
            'egyhazmegyek' => $_egyhazmegyek->map(fn($m) => ['id' => $m->id, 'name' => $m->nev])->values()->toArray(),
            'espereskeruletek' => $_espereskeruletek->map(fn($m) => ['id' => $m->id, 'name' => $m->nev])->values()->toArray(),
            'orszagok' => $_orszagok->map(fn($m) => ['id' => $m->id, 'name' => $m->nev])->values()->toArray(),
            'megyek' => $_megyek->map(fn($m) => ['id' => $m->id, 'name' => $m->nev])->values()->toArray(),
            'varosok' => $_varosok->map(fn($m) => ['id' => $m->id, 'name' => $m->nev])->values()->toArray(),
        ]);
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
