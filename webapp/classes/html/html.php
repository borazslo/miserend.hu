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
        if (isset($this->user->loggedin) AND $this->user->loggedin AND ! $this->user->checkRole('miserend')) {
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

		$loader = new \Twig\Loader\FilesystemLoader(PATH . $this->templatesPath);
		$this->twig = new \Twig\Environment($loader); //cache:
        include_once('twig_extras.php');
        $this->twig->addFilter(new \Twig\TwigFilter('miserend_date', 'twig_hungarian_date_format'));
        // DANGER: a twig declarálva van / meg van hívva a Load.php -ban is. Így ott is módosítani kellhet a filterket

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
            'title' => 'Térkép',  'mid' => 27,
            'url' => '/terkep',
            'items' => [
                [ 'title' => 'Térképen a misézőhelyek', 'url' => '/terkep' ],
                [ 'title' => 'Térképes plakátkészítő', 'url' => 'https://szentjozsefhackathon.github.io/templom-terkep/' ]
            ]
        ];
        
    }

    function loadAdminMenu() {
        $adminmenuitems = [
            ['title' => 'Miserend', 'url' => '/templom/list', 'permission' => 'miserend', 'mid' => 27,
                'items' => [
                    ['title' => 'teljes lista', 'url' => '/templom/list', 'permission' => ''],
                    ['title' => 'kezelendő észrevételek', 'url' => '/templom/list?status=Rnj&orderBy=updated_at+DESC', 'permission' => ''],

                    ['title' => 'egyházmegyei lista', 'url' => '/egyhazmegye/list', 'permission' => 'miserend'],
                    ['title' => 'kifejezések és dátumok', 'url' => '/eventscatalogue', 'permission' => 'miserend'],
                    ['title' => 'statisztika', 'url' => '/stat', 'permission' => '"any"'],
                    ['title' => 'gyóntatások', 'url' => '/confessionscatalogue', 'permission' => 'miserend'],
					
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
        //GIT version        ;
        // exec('git rev-parse --verify HEAD 2> /dev/null', $v);

        $v = trim(file_get_contents('../git_hash')); // See: (.)git/hooks/post-checkout
        //Validate short of git_hash
        if( preg_match('/^[a-zA-Z0-9]{7,8}$/i',$v,$match) ) { 
            return $v;
        }
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
    
    /**
     * Inline CSS files found in the HTML content.
     *
     * @param string $html The HTML content to process.
     * @return string The HTML content with CSS files inlined.
     */     	
    function inlineCssFiles($html) {
        // Keresd meg az összes <link> elemet, amely CSS fájlokat hivatkozik
        preg_match_all('/<link.*?href=["\'](.*?)["\'].*?rel=["\']stylesheet["\'].*?>/i', $html, $matches);

        // Az összes megtalált CSS fájl URL-je
        $cssFiles = $matches[1];

        // A CSS tartalmakat ide gyűjtjük
        $inlinedCss = '';

        foreach ($cssFiles as $cssFile) {
            // Teljes URL generálása, ha relatív útvonal
            $cssFilePath = $cssFile;
            if (strpos($cssFile, 'http') !== 0) {
                $cssFilePath = $_SERVER['DOCUMENT_ROOT'] . $cssFile;
            }

            // Ellenőrizzük, hogy a fájl létezik-e
            if (file_exists($cssFilePath)) {
                // A CSS fájl tartalmának beolvasása
                $cssContent = file_get_contents($cssFilePath);
                $inlinedCss .= "<style>\n" . $cssContent . "\n</style>\n";
            }
        }

        // Az összes <link> elem eltávolítása a HTML-ből
        $html = preg_replace('/<link.*?rel=["\']stylesheet["\'].*?>/i', '', $html);

        // Az inlined CSS hozzáadása a <head> részhez
        $html = preg_replace('/<\/head>/', $inlinedCss . '</head>', $html);

        return $html;
    }	
}
