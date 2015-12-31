<?php

namespace Html;

class Html {

    public $template;
    public $menu = array();
    public $pageTitle = 'VPP - miserend';
    public $templatesPath = 'templates';
    public $extraMeta;

    function __construct() {
        $this->input = $_REQUEST;
        $this->initPagination();
    }

    function render() {
        global $user;
        $this->user = $user;

        $this->loadMenu();
        #$this->campaign = updatesCampaign();
        if ($this->user->loggedin AND ! $this->user->checkRole('miserend')) {
            $this->mychurches = feltoltes_block();
        }
        if ($this->user->checkRole('"any"')) {
            $this->chat = chat_load();
        }

        $this->messages = getMessages();

        $this->loadTwig();
        $this->getTemplateFile();
        $this->html = $this->twig->render($this->template, (array) $this);
        $this->injectTime(microtime() - $startTime);
    }

    function loadTwig() {
        require_once 'vendor/twig/twig/lib/Twig/Autoloader.php';
        \Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem($this->templatesPath);
        $this->twig = new \Twig_Environment($loader); // cache?          
    }

    function getTemplateFile() {
        if (!isset($this->template)) {
            $className = get_class($this);
            $classPath = preg_replace("/\\\/i", "/", get_class($this));
            $classShortPath = preg_replace("/Html\//i", "", $classPath);
            $this->template = $classShortPath . ".twig";
        }
    }

    function loadMenu() {
        if ($this->user->checkRole("'any'")) {
            $this->loadAdminMenu();
        }
        if (count($user->responsible['diocese']) > 0 AND ! $user->checkRole('miserend')) {
            $this->loadResponsibleMenu();
        }
    }

    function loadAdminMenu() {
        $adminmenuitems = [
            ['title' => 'Miserend', 'url' => '/termplom/list', 'permission' => 'miserend', 'mid' => 27,
                'items' => [
                    ['title' => 'új templom', 'url' => '/templom/new', 'permission' => ''],
                    ['title' => 'lista', 'url' => '/templom/list', 'permission' => ''],
                    ['title' => 'egyházmegyei lista', 'url' => '/egyhazmegye/list', 'permission' => 'miserend'],
                    ['title' => 'kifejezések és dátumok', 'url' => '/eventscatalogue', 'permission' => 'miserend'],
                ]
            ],
            ['title' => 'Felhasználók', 'url' => '/user/catalogue', 'permission' => 'user',
                'items' => [
                    ['title' => 'új felhasználó', 'url' => '/user/new', 'permission' => 'user'],
                    ['title' => 'lista', 'url' => '/user/catalogue', 'permission' => 'user'],
                ]
            ],
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

    function injectTime($time) {
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
        if ($this->input['page']) {
            $this->pagination->active = $this->input['page'];
        }
        if ($this->input['take']) {
            $this->pagination->take = $this->input['take'];
        }
    }

}
