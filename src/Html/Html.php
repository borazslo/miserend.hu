<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html;

use App\Chat;
use App\Message;
use App\Twig\Extensions\WebpackCompatibilityExtension;
use JetBrains\PhpStorm\NoReturn;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Html
{
    public $template;
    public $menu = [];
    // public $pageTitle = 'Miserend';
    public $templatesPath = 'templates';
    public $extraMeta;

    /**
     * @internal
     */
    public $html;

    public function __construct()
    {
        $this->input = $_REQUEST;
        $this->initPagination();
    }

    protected function getContextVariables(): array
    {
        return (array) $this;
    }

    protected function addContextVariable(string $key, mixed $variable): void
    {
        $this->{$key} = $variable;
    }

    protected $user;

    public function render(): void
    {
        global $user, $config;

        $this->user = $user; // BC

        $this->addContextVariable('environment', $config['env']);
        $this->addContextVariable('githash', $this->getGitHash());
        $this->addContextVariable('user', $user);

        $this->loadMenu();
        if (isset($user->loggedin) && $user->loggedin && !$user->checkRole('miserend')) {
            $this->addContextVariable('mychurches', feltoltes_block());
        }

        if ($user->checkRole('"any"')) {
            $chat = new Chat();
            $chat->load();
            $chat = collect($chat)->toArray();

            $this->addContextVariable('chat', $chat);
        }

        $this->addContextVariable('missages', Message::getToShow());

        $this->loadTwig();
        $this->getTemplateFile();
        $this->html = $this->twig->render(strtolower($this->template), $this->getContextVariables());
        $this->injectTime();
    }

    private Environment $twig;

    private function loadTwig(): void
    {
        $loader = new FilesystemLoader(PATH.$this->templatesPath);
        $this->twig = new Environment($loader); // cache:

        $this->twig->addExtension(new WebpackCompatibilityExtension());
    }

    public function getTemplateFile()
    {
        if (!isset($this->template)) {
            $className = static::class;
            $classPath = preg_replace("/\\\/i", '/', static::class);
            $classShortPath = preg_replace('/App\/Html\//i', '', $classPath);
            $this->template = strtolower($classShortPath).'.twig'; // TODO rename .html.twig
        }
    }

    public function loadMenu()
    {
        if ($this->user->checkRole("'any'")) {
            $this->loadAdminMenu();
        }
        if (isset($this->user->responsible['diocese']) && \count($this->user->responsible['diocese']) > 0 && !$this->user->checkRole('miserend')) {
            $this->loadResponsibleMenu();
        }
        $this->menu[] = [
            'title' => 'Térkép',
            'url' => '/terkep',
        ];
    }

    public function loadAdminMenu()
    {
        $adminmenuitems = [
            ['title' => 'Miserend', 'url' => '/termplom/list', 'permission' => 'miserend', 'mid' => 27,
                'items' => [
                    ['title' => 'teljes lista', 'url' => '/templom/list', 'permission' => ''],
                    ['title' => 'egyházmegyei lista', 'url' => '/egyhazmegye/list', 'permission' => 'miserend'],
                    ['title' => 'kifejezések és dátumok', 'url' => '/eventscatalogue', 'permission' => 'miserend'],
                    ['title' => 'statisztika', 'url' => '/stat', 'permission' => '"any"'],
                    ['title' => 'egészség', 'url' => '/health', 'permission' => 'miserend'],
                    ['title' => 'API tesztelés', 'url' => '/apitest', 'permission' => 'miserend'],
                    ['title' => 'OSM kapcsolat', 'url' => '/josm', 'permission' => 'miserend'],
                ],
            ],
            ['title' => 'Felhasználók', 'url' => '/user/catalogue', 'permission' => 'user'],
        ];
        $adminmenuitems = $this->clearMenu($adminmenuitems);
        $this->menu = array_merge($this->menu, $adminmenuitems);
    }

    public function loadResponsibleMenu()
    {
        $diocesemenuitems = [
            ['title' => 'Templomok', 'url' => '/user/maintainedchurches',
                'items' => [
                    ['title' => 'módosítás', 'url' => '/user/maintainedchurches'],
                ],
            ],
        ];
        $this->menu = array_merge($this->menu, $diocesemenuitems);
    }

    public function clearMenu($menuitems)
    {
        foreach ($menuitems as $key => $item) {
            if (isset($item['permission']) && !$this->user->checkRole($item['permission'])) {
                unset($menuitems[$key]);
            } else {
                if (isset($item['items']) && \is_array($item['items'])) {
                    foreach ($item['items'] as $k => $i) {
                        if (isset($i['permission']) && !$this->user->checkRole($i['permission'])) {
                            unset($menuitems[$key][$k]);
                        } else {
                        }
                    }
                }
            }
        }

        return $menuitems;
    }

    public function setTitle(?string $title): void
    {
        $this->addContextVariable('pageTitle', $title.' | Miserend');
        $this->addContextVariable('title', $title);
    }

    public function addExtraMeta($name, $content): true
    {
        $this->extraMeta .= "\n<meta name='".$name."' content='".$content."'>";

        return true;
    }

    public function injectTime()
    {
        global $config;
        if ($config['debug'] > 0) {
            $this->html = str_replace('<!--xxx-->', (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']).' s', $this->html);
        }
    }

    public function array2this($array)
    {
        copyArrayToObject($array, $this);
    }

    #[NoReturn]
    public function redirect($url)
    {
        // http_redirect ($url,$params,$session,$status);
        header('Location: '.$url);
        exit;
    }

    public function redirectWithAnalyticsEvent($url, $event)
    {
        echo "<script type='text/javascript'>".
        '(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){'.
        '(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),'.
        'm=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)'.
        '})(window,document,"script","//www.google-analytics.com/analytics.js","ga");'.
        'ga("create", "UA-3987621-4", "miserend.hu");'.
        "ga('send','event','Search','".implode("','", $event)."');".
        "window.location = '".$url."';".
        '</script>';
        exit;
    }

    public function initPagination()
    {
        $this->pagination = new \App\Pagination();
        if (isset($this->input['page'])) {
            $this->pagination->active = $this->input['page'];
        }
        if (isset($this->input['take'])) {
            $this->pagination->take = $this->input['take'];
        }
    }

    public function getGitHash()
    {
        // GIT version        ;
        // exec('git rev-parse --verify HEAD 2> /dev/null', $v);

        if (!is_file('../git_hash')) {
            return 'DEV';
        }

        $v = trim(file_get_contents('../git_hash')); // See: (.)git/hooks/post-checkout
        // Validate short of git_hash
        if (7 == \strlen($v) && preg_match('/^[a-zA-Z0-9]{7}$/i', $v, $match)) {
            return $v;
        }

        return false;
    }

    public static function printExceptionVerbose($e, $toString = false)
    {
        $return = '<strong>'.$e->getMessage()."</strong>\n";
        foreach ($e->getTrace() as $trace) {
            if (isset($trace['class'])) {
                $return .= $trace['class'].'::'.$trace['function'].'()';
            }
            if (isset($trace['file'])) {
                $return .= $trace['file'].':'.$trace['line'].' -> '.$trace['function'].'()';
            }
            $return .= "\n";
        }

        if (!$toString) {
            echo '<pre>'.$return.'<pre>';
        }

        return $return;
    }
}
