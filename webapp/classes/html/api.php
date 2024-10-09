<?php

namespace Html;

class Api extends Html {

    public function __construct() {
        ini_set('memory_limit', '256M');

        try {
            $action = \Request::SimpletextRequired('action');
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }

        try {

            if(!isset($action)) {
                $this->redirect('https://github.com/borazslo/miserend.hu/wiki/API');
            }
            switch ($action) {
                case 'sqlite':
                    $this->redirect(DOMAIN . '/fajlok/sqlite/miserend_v' . $_REQUEST['v'] . '.sqlite3');
                    exit;
                    break;
                case 'signup':
                    $this->api = new \Api\Signup();
                    break;
                case 'login':
                    $this->api = new \Api\Login();
                    break;
                case 'user':
                    $this->api = new \Api\User();
                    break;
                case 'favorites':
                    $this->api = new \Api\Favorites();
                    break;

                case 'report':
                    $this->api = \Api\Report::factoryCreate();
                    break;

                case 'updated':
                    $this->api = new \Api\Updated();
                    break;

                case 'table':
                    $this->api = new \Api\Table();
                    break;

                case 'upload':
                    $this->api = new \Api\Upload();
                    break;

                case 'service_times':
                    $this->api = new \Api\Service_times();
                    break;   

                case 'nearby':
                    $this->api = new \Api\NearBy();
                    break;   					

                case 'church':
                    $this->api = new \Api\Church();
                    break;   					

                case 'database':
                    $this->api = new \Api\Database();
                    break;   
					
                default:
                    throw new \Exception("API action '$action' is not supported.");
            }

            $this->api->run();
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
    }

    function render() {
        //Because of the Report::factoryCreate();

        if (!isset($this->api)) {
            $this->html = json_encode(['error' => 1, 'text' => $this->error]);
            return;
        } elseif (isset($this->error)) {
            $this->api->return = ['error' => '1', 'text' => $this->error];
        }

        if (!$this->api->format)
            $this->api->format = 'text';
        $renderfunction = 'render' . ucfirst($this->api->format);
        if (method_exists($this->api, $renderfunction)) {
            $this->api->$renderfunction();
        } else {
            $this->$renderfunction();
        }
    }

    public function renderText() {
        if (is_array($this->api->return)) {
            $this->html = '';
            foreach ($this->api->return as $key => $value) {
                $this->html .= $key . ": \"" . $value . "\";\n";
            }
        } else {
            $this->html = $this->api->return;
        }
    }

    public function renderJson() {
        if (!isset($this->api->return['error'])) {
            $this->api->return['error'] = 0;
        }
        $this->html = json_encode($this->api->return);
    }

    public function renderCsv() {
        if (is_array($this->api->return)) {

            //TODO: a szöveg nem tartalmazhatja az elválasztó karaktert, különben gond van.
            $columnNames = array_keys($this->api->return[$this->api->tableName][0]);
            $this->html = implode($this->api->delimiter, $columnNames) . ";\n";
            foreach ($this->api->return[$this->api->tableName] as $row) {
                $this->html .= implode($this->api->delimiter, $row) . ";\n";
            }
        } else {
            $this->html = $this->api->return;
        }
    }

}
