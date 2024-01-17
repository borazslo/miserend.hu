<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html;

use App\Api\Favorites;
use App\Api\Login;
use App\Api\NearBy;
use App\Api\Report;
use App\Api\Service_times;
use App\Api\Signup;
use App\Api\Table;
use App\Api\Updated;
use App\Api\Upload;
use App\Api\User;
use App\Request;

class Api extends Html
{
    private $api;
    private $error;

    public function __construct()
    {
        ini_set('memory_limit', '256M');

        try {
            $action = Request::SimpletextRequired('action');
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }

        try {
            if (!isset($action)) {
                $this->redirect('https://github.com/borazslo/miserend.hu/wiki/API');
            }
            switch ($action) {
                case 'sqlite':
                    $this->redirect(DOMAIN.'/fajlok/sqlite/miserend_v'.$_REQUEST['v'].'.sqlite3');

                case 'signup':
                    $this->api = new Signup();
                    break;

                case 'login':
                    $this->api = new Login();
                    break;

                case 'user':
                    $this->api = new User();
                    break;

                case 'favorites':
                    $this->api = new Favorites();
                    break;

                case 'report':
                    $this->api = Report::factoryCreate();
                    break;

                case 'updated':
                    $this->api = new Updated();
                    break;

                case 'table':
                    $this->api = new Table();
                    break;

                case 'upload':
                    $this->api = new Upload();
                    break;

                case 'service_times':
                    $this->api = new Service_times();
                    break;

                case 'nearby':
                    $this->api = new NearBy();
                    break;

                default:
                    throw new \Exception("API action '$action' is not supported.");
            }

            $this->api->run();
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
    }

    public function render(): void
    {
        // Because of the Report::factoryCreate();

        if (!isset($this->api)) {
            $this->html = json_encode(['error' => 1, 'text' => $this->error]);

            return;
        } elseif (isset($this->error)) {
            $this->api->return = ['error' => '1', 'text' => $this->error];
        }

        if (!$this->api->format) {
            $this->api->format = 'text';
        }
        $renderfunction = 'render'.ucfirst($this->api->format);
        if (method_exists($this->api, $renderfunction)) {
            $this->api->$renderfunction();
        } else {
            $this->$renderfunction();
        }
    }

    public function renderText()
    {
        if (\is_array($this->api->return)) {
            $this->html = '';
            foreach ($this->api->return as $key => $value) {
                $this->html .= $key.': "'.$value."\";\n";
            }
        } else {
            $this->html = $this->api->return;
        }
    }

    public function renderJson()
    {
        if (!isset($this->api->return['error'])) {
            $this->api->return['error'] = 0;
        }
        $this->html = json_encode($this->api->return);
    }

    public function renderCsv()
    {
        if (\is_array($this->api->return)) {
            // TODO: a szöveg nem tartalmazhatja az elválasztó karaktert, különben gond van.
            $columnNames = array_keys($this->api->return[$this->api->tableName][0]);
            $this->html = implode($this->api->delimiter, $columnNames).";\n";
            foreach ($this->api->return[$this->api->tableName] as $row) {
                $this->html .= implode($this->api->delimiter, $row).";\n";
            }
        } else {
            $this->html = $this->api->return;
        }
    }
}
