<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html;

use App\Legacy\Services\ConstantsProvider;
use App\Legacy\UserRepository;
use App\Model\Photo;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Home extends Html
{
    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + [
            ConstantsProvider::class => ConstantsProvider::class,
            UserRepository::class => UserRepository::class,
        ];
    }

    public function getConstantsProvider(): ConstantsProvider
    {
        return $this->container->get(ConstantsProvider::class);
    }

    public function getUserRepository(): UserRepository
    {
        return $this->container->get(UserRepository::class);
    }

    public function main(Request $request): Response
    {
        if ($request->query->has('templom')) {
            return new RedirectResponse('/templom/'.$request->query->getInt('templom'), 301);
        }

        $user = $this->getUser();

        return $this->render('home.twig', [
            'searchform' => $this->getSearchForm(),
            'favorites' => $this->getFavorites(),
            'alert' => LiturgicalDayAlert('html'),
            'photo' => $this->getRandomPhoto(),
        ]);
    }

    private function getFavorites(): iterable
    {
        $user = $this->getUser();
        return $this->getUserRepository()->getFavorites($user);
    }

    private function getRandomPhoto()
    {
        $photo = Photo::big()->vertical()->where('flag', 'i')->orderbyRaw('RAND()')->first();
        if ($photo->church) { // TODO: Van, hogy a random képhez nem is tartozik templom. Valami régi hiba miatt.
            $photo->church->location;
        }

        return $photo;
    }

    private function getSearchForm(): array
    {
        $ma = date('Y-m-d');
        $holnap = date('Y-m-d', time() + 86400);
        $mikor = '8:00-19:00';

        $attributes = $this->getConstantsProvider()->getAttributes();
        $languages = $this->getConstantsProvider()->getLanguages();

        $manager = $this->getDatabaseManager();

        $espkers = $manager->table('espereskerulet')
            ->select('id', 'ehm', 'nev')
            ->get();

        foreach ($espkers as $espker) {
            $espkerT[$espker->ehm][$espker->id] = $espker->nev; // code...
        }

        // MISEREND űRLAP
        $searchform = [
            'kulcsszo' => [
                'name' => 'kulcsszo',
                'id' => 'keyword',
                'size' => 20,
                'class' => 'keresourlap',
                'placeholder' => 'kulcsszó',
                'attr' => [
                    'data-controller' => 'autocomplete',
                    'data-endpoint' => '/ajax/AutocompleteKeyword',
                ],
                'label' => 'Templom',
                'labelAttr' => [
                    'class' => 'col-sm-4 control-label',
                ],
            ],
            'varos' => [
                'name' => 'varos',
                'size' => 20,
                'id' => 'varos',
                'class' => 'keresourlap',
                'placeholder' => 'település',
                'attr' => [
                    'data-controller' => 'autocomplete',
                    'data-endpoint' => '/ajax/AutocompleteCity',
                ],
                'label' => 'település',
                'labelAttr' => [
                    'class' => 'col-sm-4 control-label',
                ],
            ],
            'hely' => [
                'name' => 'hely',
                'size' => 20,
                'id' => 'varos',
                'class' => 'keresourlap',
            ],
            'tavolsag' => [
                'name' => 'tavolsag',
                'size' => 1,
                'id' => 'tavolsag',
                'class' => 'keresourlap',
                'value' => 4,
            ],
        ];

        $searchform['ehm'] = [
            'name' => 'ehm',
            'class' => 'keresourlap',
            'label' => 'egyházmegye',
            'labelAttr' => [
                'class' => 'col-sm-4 control-label',
            ],
            'id' => 'egyhazmegye',
            'options' => [
                0 => 'mindegy',
            ],
        ];

        $egyhmegyes = $manager->table('egyhazmegye')
            ->select('id', 'nev')
            ->where('ok', 'i')
            ->orderBy('sorrend')
            ->get();

        foreach ($egyhmegyes as $egyhmegye) {
            $searchform['ehm']['options'][$egyhmegye->id] = $egyhmegye->nev;
        }

        foreach ($espkerT as $ehm => $espker) {
            $searchform['espker'][$ehm] = [
                'name' => 'espker',
                'id' => 'ehm'.$ehm,
                'class' => 'keresourlap',
            ];
            $searchform['espker'][$ehm]['options'][0] = 'mindegy';
            if (\is_array($espker)) {
                foreach ($espker as $espid => $espnev) {
                    $searchform['espker'][$ehm]['options'][$espid] = $espnev;
                }
            }
        }

        $searchform['gorog'] = [
            'type' => 'checkbox',
            'name' => 'gorog',
            'id' => 'gorog',
            'class' => 'form-check-input',
            'value' => 'gorog',
            'label' => 'csak görögkatolikus',
            'labelAttr' => [
                'class' => 'form-check-label',
            ],
        ];

        $searchform['tnyelv'] = [
            'name' => 'tnyelv',
            'id' => 'tnyelv',
            'class' => 'keresourlap',
            'options' => [
                0 => 'bármilyen',
            ],
            'label' => 'ahol van adott nyelvű mise is',
            'labelAttr' => [
                'class' => 'col-sm-4 control-label',
            ],
        ];
        foreach ($languages as $abbrev => $language) {
            $searchform['tnyelv']['options'][$abbrev] = $language['name'];
        }

        // Mikor
        $mainap = date('w');
        if (0 == $mainap) {
            $vasarnap = $ma;
        } else {
            $kulonbseg = 7 - $mainap;
            $vasarnap = date('Y-m-d', time() + (86400 * $kulonbseg));
        }
        $searchform['mikor'] = [
            'name' => 'mikor',
            'id' => 'mikor',
            'class' => 'keresourlap rounded-1',
            'options' => [
                $vasarnap => 'vasárnap',
                $ma => 'ma',
                $holnap => 'holnap',
                'x' => 'adott napon:',
            ],
            'label' => 'Mise',
            'labelAttr' => [
                'class' => 'col-sm-4 control-label',
            ],
        ];
        $searchform['mikordatum'] = [
            'name' => 'mikordatum',
            'id' => 'md',
            'class' => 'keresourlap datepicker d-none',
            'size' => '10',
            'value' => $ma,
        ];
        $searchform['mikor2'] = [
            'name' => 'mikor2',
            'id' => 'mikor2',
            'class' => 'keresourlap rounded-1',
            'options' => [
                0 => 'egész nap',
                'de' => 'délelőtt',
                'du' => 'délután',
                'x' => 'adott időben:',
            ],
        ];
        $searchform['mikorido'] = [
            'name' => 'mikorido',
            'id' => 'md2',
            'class' => 'keresourlap  d-none',
            'size' => '7',
            'value' => $mikor,
        ];

        // languages
        $searchform['nyelv'] = [
            'name' => 'nyelv',
            'id' => 'nyelv',
            'class' => 'keresourlap',
            'options' => [
                0 => 'mindegy',
            ],
            'label' => 'nyelv',
            'labelAttr' => [
                'class' => 'col-sm-4 control-label',
            ],
        ];
        foreach ($languages as $abbrev => $language) {
            $searchform['nyelv']['options'][$abbrev] = $language['name'];
        }

        // group music
        $music['na'] = 'meghatározatlan';
        foreach ($attributes as $abbrev => $attribute) {
            if ('music' == $attribute['group']) {
                $music = [$abbrev => $attribute['name']] + $music;
            }
        }

        $searchform['zene'] = [];
        foreach ($music as $value => $label) {
            $searchform['zene'][] = [
                'type' => 'checkbox',
                'name' => 'zene[]',
                'class' => 'form-check-input',
                'value' => $value,
                'checked' => true,
                'label' => $label,
                'labelAttr' => [
                    'class' => 'form-check-label'.('na' === $value ? ' fst-italic' : ''),
                ],
            ];
        }

        // group age
        $age['na'] = 'meghatározatlan';
        foreach ($attributes as $abbrev => $attribute) {
            if ('age' == $attribute['group']) {
                $age = [$abbrev => $attribute['name']] + $age;
            }
        }
        foreach ($age as $value => $label) {
            $searchform['kor'][] = [
                'type' => 'checkbox',
                'name' => 'kor[]',
                'class' => 'form-check-input',
                'value' => $value,
                'checked' => true,
                'label' => $label,
                'labelAttr' => [
                    'class' => 'form-check-label'.('na' === $value ? ' fst-italic' : ''),
                ],
            ];
        }

        // group rite
        $searchform['ritus'] = [
            'name' => 'ritus',
            'id' => 'ritus',
            'class' => 'keresourlap',
            'options' => [
                0 => 'mindegy',
            ],
            'label' => 'rítus',
            'labelAttr' => [
                'class' => 'col-sm-4 control-label',
            ],
        ];
        foreach ($attributes as $abbrev => $attribute) {
            if ('liturgy' == $attribute['group'] && isset($attribute['isitmass'])) {
                $searchform['ritus']['options'][$abbrev] = $attribute['name'];
            }
        }

        $searchform['ige'] = [
            'type' => 'checkbox',
            'name' => 'liturgy[]',
            'id' => 'liturgy',
            'checked' => true,
            'class' => 'keresourlap',
            'value' => 'ige',
            'label' => 'igeliturgiák is',
            'labelAttr' => [
                'class' => 'col-sm-4 control-label',
            ],
        ];

        return $searchform;
    }
}
