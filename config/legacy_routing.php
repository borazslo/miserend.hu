<?php

use App\Html\Church;
use App\Html\Home;
use App\Html\Map;
use App\Html\Remark;
use App\Html\StaticPage;
use App\Html\User;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->add('home', new Route(
    path: '/',
    defaults: [
        '_class' => Home::class,
    ]
));

$collection->add('map', new Route(
    path: '/terkep',
    defaults: [
        '_class' => Map::class,
    ])
);

$collection->add('church_view', new Route(
    path: '/templom/{church_id}',
    defaults: [
        '_class' => Church\Church::class,
    ],
    requirements: [
        'church_id' => '\d+'
    ]
));

$collection->add('church_remarks', new Route(
    path: '/templom/{church_id}/eszrevetelek',
    defaults: [
        '_class' => Remark::class,
    ],
    requirements: [
        'church_id' => '\d+'
    ]
));

$collection->add('church_list', new Route('/templom/list', defaults: [
    '_class' => Church\Catalogue::class,
]));

$collection->add('church_new', new Route('/templom/new', defaults: [
    '_class' => Church\Edit::class,
]));

$collection->add('user_new', new Route('/user/new', defaults: [
    '_class' => User\Edit::class,
]));

$collection->add('user_edit', new Route('/user/edit', defaults: [
    '_class' => User\Edit::class,
]));

$collection->add('about', new Route('/impresszum', defaults: [
    '_class' => StaticPage::class,
]));

$collection->add('gdpr', new Route('/gdpr', defaults: [
    '_class' => StaticPage::class,
]));

$collection->add('terms_and_conditions', new Route('/hazirend', defaults: [
    '_class' => StaticPage::class,
]));

/*
["^templom\/([0-9]{1,5})\/eszrevetelek$", "remark/list/$1"],
            ["^templom\/([0-9]{1,5})\/ujeszrevetel$", "remark/addform/$1"],
            ["^templom\/([0-9]{1,5})\/ujkep$", "uploadimage/$1"],
            ["^remark\/([0-9]{1,5})\/feedback", "email/remarkfeedback/$1"],
            ["^egyhazmegye\/list", "diocesecatalogue"],
*/

return $collection;