<?php

namespace Html;

class Html {

    public $template;
    public $menu = array();
    //public $pageTitle = 'Miserend';
    public $templatesPath = 'templates';
    public $extraMeta;

    function __construct() {
        $this->input = $_REQUEST;
        $this->initPagination();
    }

    function render() {
        global $user, $config;

        $this->environment = $config['env'];
        $this->githash = $this->getGitHash();
        $this->user = $user;

        $this->loadMenu();
        if ($this->user->loggedin AND ! $this->user->checkRole('miserend')) {
            $this->mychurches = feltoltes_block();
        }
        if ($this->user->checkRole('"any"')) {
            $this->chat = new \Chat;
            $this->chat->load();
            $this->chat = collect($this->chat)->toArray();
        }

        $this->messages = \Message::getToShow();

        $this->loadTwig();
        $this->getTemplateFile();
        $this->html = $this->twig->render(strtolower($this->template), (array) $this);
        $this->injectTime();
    }

    function loadTwig() {
        #require_once PATH.'vendor/twig/twig/lib/Twig/Autoloader.php';        
        #\Twig_Autoloader::register();        
        $loader = new \Twig_Loader_Filesystem(PATH . $this->templatesPath);
        $this->twig = new \Twig_Environment($loader); // cache?                  
    }

    function getTemplateFile() {
        if (!isset($this->template)) {
            $className = get_class($this);
            $classPath = preg_replace("/\\\/i", "/", get_class($this));
            $classShortPath = preg_replace("/html\//i", "", $classPath);
            $this->template = $classShortPath . ".twig";
        }
    }

    function loadMenu() {
        if ($this->user->checkRole("'any'")) {
            $this->loadAdminMenu();
        }
        if (isset($this->user->responsible['diocese']) AND count($this->user->responsible['diocese']) > 0 AND ! $this->user->checkRole('miserend')) {
            $this->loadResponsibleMenu();
        }
        $this->menu[] = [
            'title' => 'Térkép',
            'url' => '/terkep'
        ];
    }

    function loadAdminMenu() {
        $adminmenuitems = [
            ['title' => 'Miserend', 'url' => '/termplom/list', 'permission' => 'miserend', 'mid' => 27,
                'items' => [
                    ['title' => 'teljes lista', 'url' => '/templom/list', 'permission' => ''],
                    ['title' => 'egyházmegyei lista', 'url' => '/egyhazmegye/list', 'permission' => 'miserend'],
                    ['title' => 'kifejezések és dátumok', 'url' => '/eventscatalogue', 'permission' => 'miserend'],
                    ['title' => 'statisztika', 'url' => '/stat', 'permission' => '"any"'],
                    ['title' => 'API tesztelés', 'url' => '/apitest', 'permission' => 'miserend'],
                    ['title' => 'OSM kapcsolat', 'url' => '/josm', 'permission' => 'miserend'],
                ]
            ],
            ['title' => 'Felhasználók', 'url' => '/user/catalogue', 'permission' => 'user'],
        ];
        $adminmenuitems = $this->clearMenu($adminmenuitems);
        $this->menu = array_merge($this->menu, $adminmenuitems);
    }

    function loadResponsibleMenu() {
        $diocesemenuitems = [
            ['title' => 'Templomok', 'url' => '/user/maintainedchurches',
                'items' => [
                    ['title' => 'módosítás', 'url' => '/user/maintainedchurches'],
                ]
            ],
        ];
        $this->menu = array_merge($this->menu, $diocesemenuitems);
    }

    function clearMenu($menuitems) {
        foreach ($menuitems as $key => $item) {
            if (isset($item['permission']) AND ! $this->user->checkRole($item['permission'])) {
                unset($menuitems[$key]);
            } else {
                if (isset($item['items']) AND is_array($item['items'])) {
                    foreach ($item ['items'] as $k => $i) {
                        if (isset($i['permission']) AND ! $this->user->checkRole($i['permission'])) {
                            unset($menuitems[$key][$k]);
                        } else {
                            
                        }
                    }
                }
            }
        }
        return $menuitems;
    }

    function setTitle($title) {
        $this->pageTitle = $title . " | Miserend";
        $this->title = $title;
    }

    function addExtraMeta($name, $content) {
        $this->extraMeta .= "\n<meta name='" . $name . "' content='" . $content . "'>";
        return true;
    }

    function injectTime() {
        global $config;
        if ($config['debug'] > 0) {
            $this->html = str_replace('<!--xxx-->', ( microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"] ) . " s", $this->html);
        }
    }

    function array2this($array) {
        copyArrayToObject($array, $this);
    }

    function redirect($url) {
        # http_redirect ($url,$params,$session,$status);
        header("Location: " . $url);
        exit;
    }

    function redirectWithAnalyticsEvent($url, $event) {
        echo "<script type='text/javascript'>" .
        "(function(i,s,o,g,r,a,m){i[\"GoogleAnalyticsObject\"]=r;i[r]=i[r]||function(){" .
        "(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o)," .
        "m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)" .
        "})(window,document,\"script\",\"//www.google-analytics.com/analytics.js\",\"ga\");" .
        "ga(\"create\", \"UA-3987621-4\", \"miserend.hu\");" .
        "ga('send','event','Search','" . implode("','", $event) . "');" .
        "window.location = '" . $url . "';" .
        "</script>";
        exit;
    }

    function initPagination() {
        $this->pagination = new \Pagination();
        if (isset($this->input['page'])) {
            $this->pagination->active = $this->input['page'];
        }
        if (isset($this->input['take'])) {
            $this->pagination->take = $this->input['take'];
        }
    }

    function getGitHash() {
        //GIT version
        exec('git rev-parse --verify HEAD 2> /dev/null', $output);
        if (isset($output[0]) AND $output[0] != '')
            return $output[0];
        return false;
    }
    
    static function printExceptionVerbose($e, $toString = false) {
        
        $return = "<strong>".$e->getMessage()."</strong>\n";        
        foreach ($e->getTrace() as $trace) {
            if (isset($trace['class']))
                $return .= $trace['class'] . "::" . $trace['function'] . "()";
            if (isset($trace['file']))
                $return .= $trace['file'] . ":" . $trace['line'] . " -> " . $trace['function'] . "()";
            $return .= "\n";
        }

        if(!$toString)
            echo "<pre>".$return."<pre>";
       
        return $return;
    }

}
