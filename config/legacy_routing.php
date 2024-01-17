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
    'home' => ['/', Html\Home::class],
    'map' => ['/terkep', Html\Map::class],
    'church_list' => ['/templom/list', Church\Catalogue::class],
    'church_new' => ['/templom/new', Church\Edit::class],
    'user_new' => ['/user/new', User\Edit::class],
    'user_edit' => ['/user/edit', User\Edit::class],
    'ajax_autocomplete_keyword' => ['/ajax/AutocompleteKeyword', Ajax\AutocompleteKeyword::class],
    'ajax_autocomplete_city' => ['/ajax/AutocompleteCity', Ajax\AutocompleteKeyword::class],
    'ajax_boundarygeojson' => ['/ajax/boundarygeojson', Ajax\BoundaryGeoJson::class],
    'ajax_churchesinbbox' => ['/ajax/churchesinbbox', Ajax\ChurchesInBBox::class],
    'ajax_churclink' => ['/ajax/churclink', Ajax\ChurchLink::class],
    'about' => ['/impresszum', Html\StaticPage::class],
    'gdpr' => ['/gdpr', Html\StaticPage::class],
    'terms_and_conditions' => ['/hazirend', Html\StaticPage::class],
];

foreach ($simpleLegacyRoutes as $routeName => [$path, $className]) {
    $collection->add($routeName, new Route(
        path: $path,
        defaults: [
            '_class' => $className,
        ]
    ));
}

$complexLegacyRoutes = [
    'church_view' => ['/templom/{church_id}', Church\Church::class, ['church_id' => '\d+']],
    'church_remarks' => ['/templom/{church_id}/eszrevetelek', Html\Remark::class, ['church_id' => '\d+']],
];

foreach ($complexLegacyRoutes as $routeName => [$path, $className, $requirements]) {
    $collection->add($routeName, new Route(
        path: $path,
        defaults: [
            '_class' => $className,
        ],
        requirements: $requirements,
    ));
}

$symfonyRoutes = [
    'wdt' => '/_wdt/{token}',
    'profiler' => '/_profiler/{token}',
];

foreach ($symfonyRoutes as $routeName => $routePath) {
    $collection->add($routeName, new Route($routePath, defaults: [
        'handler' => 'symfony',
    ]));
}

/*
["^templom\/([0-9]{1,5})\/eszrevetelek$", "remark/list/$1"],
            ["^templom\/([0-9]{1,5})\/ujeszrevetel$", "remark/addform/$1"],
            ["^templom\/([0-9]{1,5})\/ujkep$", "uploadimage/$1"],
            ["^remark\/([0-9]{1,5})\/feedback", "email/remarkfeedback/$1"],
            ["^egyhazmegye\/list", "diocesecatalogue"],
*/

return $collection;
