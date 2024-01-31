<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html;

use App\Chat;
use App\Legacy\ConfigProvider;
use App\Legacy\ConstantsProvider;
use App\Legacy\ContainerAwareInterface;
use App\Legacy\MessageRepository;
use App\Legacy\Security;
use App\Pagination;
use App\User;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\DatabaseManager;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;
use Twig\Environment;

class Html implements ContainerAwareInterface, ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    /**
     * @internal
     */
    public $template;

    /**
     * @internal
     */
    public $user;

    public $menu = [];
    // public $pageTitle = 'Miserend';
    public $templatesPath = 'templates';
    public $extraMeta;

    /**
     * @internal
     */
    public $html;

    //    public function __construct()
    //    {
    //        $this->input = $_REQUEST;
    //        $this->initPagination();
    //    }

    /**
     * @deprecated
     */
    protected function getContextVariables(): array
    {
        return (array) $this;
    }

    /**
     * @deprecated
     */
    protected function addContextVariable(string $key, mixed $variable): void
    {
        $this->{$key} = $variable;
    }

    private function getGlobals(): array
    {
        $user = $this->getUser();
        $variables = [
            'environment' => $this->getConfig()['env'],
            'githash' => $this->getGitHash(),
            'user' => $user,
            'menu' => $this->getMenu(),
        ];

        if ($user->getLoggedin() && !$user->checkRole('miserend')) {
            $variables['mychurches'] = feltoltes_block();
        }

        if ($security->isGranted('"any"')) {
            $variables['chat'] = true;
        }

        $variables['messages'] = $this->getMessageRepository()->getToShow();

        return $variables;
    }

    public function render(string $viewName, array $context = [], int $httpStatus = 200): Response
    {
        $context += $this->getGlobals();

        return new Response($this->renderView($viewName, $context), status: $httpStatus);
    }

    protected function renderView(string $viewName, array $context = []): string
    {
        return $this->getTwig()->render($viewName, $context);
    }

    protected function getTwig(): Environment
    {
        return $this->container->get(Environment::class);
    }

    protected function getUser(): ?User
    {
        return $this->getSecurity()->getUser() ?? $this->user;
    }

    protected function getSecurity(): Security
    {
        return $this->container->get(Security::class);
    }

    protected function getDatabaseManager(): DatabaseManager
    {
        return $this->container->get(Manager::class)->getDatabaseManager();
    }

    protected function getConfig(): array
    {
        return $this->container->get(ConfigProvider::class)->getConfig();
    }

    protected function getMessageRepository(): MessageRepository
    {
        return $this->container->get(MessageRepository::class);
    }

    protected function getConstants(): ConstantsProvider
    {
        return $this->container->get(ConstantsProvider::class);
    }

    protected function getChat(): Chat
    {
        return $this->container->get(Chat::class);
    }

    public static function getSubscribedServices(): array
    {
        return [
            Environment::class => Environment::class,
            Manager::class => Manager::class,
            ConfigProvider::class => ConfigProvider::class,
            Security::class => Security::class,
            MessageRepository::class => MessageRepository::class,
            ConstantsProvider::class => ConstantsProvider::class,
        ];
    }

    protected function getMenu(): array
    {
        $user = $this->getUser();

        $menu = [];
        if ($this->getSecurity()->isGranted("'any'")) {
            $menu = $this->getAdminMenu();
        }
        if (isset($user->getResponsible()['diocese']) && \count($user->getResponsible()['diocese']) > 0 && !$user->checkRole('miserend')) {
            $menu += $this->getResponsibleMenu();
        }

        $menu[] = [
            'title' => 'Térkép',
            'url' => '/terkep',
        ];

        return $menu;
    }

    public function getAdminMenu(): array
    {
        $adminMenuItems = [
            [
                'title' => 'Miserend',
                'url' => '/termplom/list',
                'permission' => 'miserend',
                'mid' => 27,
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

        return $this->clearMenu($adminMenuItems);
    }

    public function getResponsibleMenu(): array
    {
        return [
            [
                'title' => 'Templomok',
                'url' => '/user/maintainedchurches',
                'items' => [
                    [
                        'title' => 'módosítás',
                        'url' => '/user/maintainedchurches',
                    ],
                ],
            ],
        ];
    }

    private function clearMenu($menuitems): array
    {
        $user = $this->getUser();
        foreach ($menuitems as $key => $item) {
            if (isset($item['permission']) && !$user->checkRole($item['permission'])) {
                unset($menuitems[$key]);
            } else {
                if (isset($item['items']) && \is_array($item['items'])) {
                    foreach ($item['items'] as $k => $i) {
                        if (isset($i['permission']) && !$user->checkRole($i['permission'])) {
                            unset($menuitems[$key][$k]);
                        } else {
                        }
                    }
                }
            }
        }

        return $menuitems;
    }

    /**
     * @deprecated
     * @see self::createTitleVariables()
     */
    public function setTitle()
    {
    }

    /**
     * @return array{
     *     pageTitle: string,
     *     title: string
     * }
     */
    protected function createTitleVariables(string $title): array
    {
        return [
            'pageTitle' => $title.' | Miserend',
            'title' => $title,
        ];
    }

    public function addExtraMeta($name, $content): true
    {
        $this->extraMeta .= "\n<meta name='".$name."' content='".$content."'>";

        return true;
    }

    // Todo restore functionality
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

    public function initPagination(): Pagination
    {
        $pagination = new Pagination();
        if (isset($this->input['page'])) {
            $pagination->active = $this->input['page'];
        }
        if (isset($this->input['take'])) {
            $pagination->take = $this->input['take'];
        }

        return $pagination;
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
        if (\strlen($v) == 7 && preg_match('/^[a-zA-Z0-9]{7}$/i', $v, $match)) {
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
