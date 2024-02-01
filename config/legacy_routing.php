<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use App\Html;
use App\Html\Ajax;
use App\Html\Church;
use App\Html\User;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$collection = new RouteCollection();

$simpleLegacyRoutes = [
    'church_new' => ['/templom/new', Church\Edit::class],
    'user_edit' => ['/user/edit', User\Edit::class],
    'ajax_autocomplete_keyword' => ['/ajax/AutocompleteKeyword', Ajax\AutocompleteKeyword::class],
    'ajax_autocomplete_city' => ['/ajax/AutocompleteCity', Ajax\AutocompleteKeyword::class],
    'ajax_boundarygeojson' => ['/ajax/boundarygeojson', Ajax\BoundaryGeoJson::class],
    'ajax_churclink' => ['/ajax/churclink', Ajax\ChurchLink::class],
];

foreach ($simpleLegacyRoutes as $routeName => [$path, $className]) {
    $collection->add($routeName, new Route(
        path: $path,
        defaults: [
            '_class' => $className,
        ]
    ));
}

$simpleLegacyRouteWithMethod = [
    'home' => ['/', Html\Home::class, 'main'],
    'map' => ['/terkep', Html\Map::class, 'leaflet'],
    'ajax_churchesinbbox' => ['/ajax/churchesinbbox', Church\Church::class, 'inBbox'],
    'stats' => ['/stat', Html\Stat::class, 'stat'],

//    'user_new' => ['/user/new', User\Edit::class, 'registration'], => symfony
    'user_profile' => ['/user/edit', User\Edit::class, 'edit'],
    'user_list' => ['/user/catalogue', User\Catalogue::class, 'list'],

    'church_list' => ['/templom/list', Church\Catalogue::class, 'list'],
    'church_create' => ['/church/create', Church\Create::class, 'create'],
    'church_favorite' => ['/church/favorite', Html\Ajax\Favorite::class, 'favorite'],
    'ajax_church_favorite' => ['/ajax/favorite', Html\Ajax\Favorite::class, 'favorite'],

    'ajax_chat_load' => ['/ajax/chat/load', Html\Ajax\Chat::class, 'load'],
    'ajax_chat_send' => ['/ajax/chat/send', Html\Ajax\Chat::class, 'send'],
    'ajax_chat_users' => ['/ajax/chat/users', Html\Ajax\Chat::class, 'users'],

    'about' => ['/impresszum', Html\StaticPage::class, 'staticPage'],
    'gdpr' => ['/gdpr', Html\StaticPage::class, 'staticPage'],
    'terms_and_conditions' => ['/hazirend', Html\StaticPage::class, 'staticPage'],
];

foreach ($simpleLegacyRouteWithMethod as $routeName => [$path, $className, $method]) {
    $collection->add($routeName, new Route(
        path: $path,
        defaults: [
            '_class' => $className,
            '_method' => $method,
        ]
    ));
}

$complexLegacyRoutes = [
    'church_remarks_list' => [
        '/remark/list/{church_id}',
        Html\Remark::class,
        'list',
        ['church_id' => '\d+'],
    ],
    'church_remarks_list_alias' => [
        '/templom/{church_id}/eszrevetelek',
        Html\Remark::class,
        'list',
        ['church_id' => '\d+'],
    ],
    'church_change_holder' => [
        '/templom/{church_id}/changeholders',
        Church\ChangeHolders::class,
        'form',
        ['church_id' => '\d+'],
    ],
    'church_remarks_add' => [
        '/remark/add/{church_id}',
        Html\Remark::class,
        'postAdd',
        ['church_id' => '\d+'],
    ],
    'church_image_add' => [
        '/templom/{church_id}/ujkep',
        Church\EditPhotos::class,
        'add',
        ['church_id' => '\d+'],
    ],

    'user_edit' => [
        '/user/{user_id}/edit',
        User\Edit::class,
        'edit',
        ['user_id' => '\d+'],
    ],
];

foreach ($complexLegacyRoutes as $routeName => [$path, $className, $method, $requirements]) {
    $collection->add($routeName, new Route(
        path: $path,
        defaults: [
            '_class' => $className,
            '_method' => $method,
        ],
        requirements: $requirements,
    ));
}

$collection->add('church_remarks_new', new Route(
    path: '/templom/{church_id}/ujeszrevetel',
    defaults: [
        '_class' => Html\Remark::class,
        '_method' => 'add',
    ],
    requirements: ['church_id' => '\d+'],
    methods: ['GET', 'POST']
));

$symfonyRoutes = [
    'church_view' => '/templom/{church_id}',
    'wdt' => '/_wdt/{token}',
    'profiler' => '/_profiler/{token}',
    'app_login' => '/bejelentkezes',
    'app_logout' => '/kijelentkezes',
    'user_profile' => '/profil',
    'user_new' => '/user/new',
    'user_registration' => '/regisztracio',
];

foreach ($symfonyRoutes as $routeName => $routePath) {
    $collection->add($routeName, new Route($routePath, defaults: [
        'handler' => 'symfony',
    ]));
}

$collection->add('church_view_slug', new Route('/templom/{church_id}/{slug}', defaults: [
    'handler' => 'symfony',
], requirements: [
    'slug' => '((?!changeholder|ujeszrevetel).)*',
]));

/*
            ["^remark\/([0-9]{1,5})\/feedback", "email/remarkfeedback/$1"],
            ["^egyhazmegye\/list", "diocesecatalogue"],
*/

return $collection;
