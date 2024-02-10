<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Types;

use App\Entity\Deanery;
use App\Entity\Diocese;
use App\Legacy\Services\ConstantsProvider;
use App\Repository\DioceseRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ChurchMassSearchType extends AbstractType
{
    public function __construct(
        private readonly ConstantsProvider $constantsProvider,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //        'kulcsszo' => [
        //        'name' => 'kulcsszo',
        //        'id' => 'keyword',
        //        'size' => 20,
        //        'class' => 'keresourlap',
        //        'placeholder' => 'kulcsszó',
        //        'label' => 'Templom',
        //        'labelAttr' => [
        //            'class' => 'col-sm-4 control-label',
        //        ],
        //    ],

        $builder->add('church', TextType::class, [
            'label' => 'Templom',
            'required' => false,
            'attr' => [
                'placeholder' => 'Kulcsszó',
                'data-controller' => 'autocomplete',
                'data-endpoint' => '/ajax/AutocompleteKeyword',
            ],
        ]);

        //        'varos' => [
        //        'name' => 'varos',
        //        'size' => 20,
        //        'id' => 'varos',
        //        'class' => 'keresourlap',
        //        'placeholder' => 'település',
        //        'label' => 'település',
        //        'labelAttr' => [
        //            'class' => 'col-sm-4 control-label',
        //        ],
        //    ],

        $builder->add('city', TextType::class, [
            'label' => 'Település',
            'required' => false,
            'attr' => [
                'placeholder' => 'Település',
                'data-controller' => 'autocomplete',
                'data-endpoint' => '/ajax/AutocompleteCity',
            ],
        ]);

        //        $searchform['ehm'] = [
        //            'name' => 'ehm',
        //            'class' => 'keresourlap',
        //            'label' => '',
        //            'labelAttr' => [
        //                'class' => 'col-sm-4 control-label',
        //            ],
        //            'id' => 'egyhazmegye',
        //            'options' => [
        //                0 => 'mindegy',
        //            ],
        //        ];
        //
        //        $egyhmegyes = $manager->table('egyhazmegye')
        //            ->select('id', 'nev')
        //            ->where('ok', 'i')
        //            ->orderBy('sorrend')
        //            ->get();

        $builder->add('diocese', EntityType::class, [
            'label' => 'Egyházmegye',
            'required' => false,
            'placeholder' => 'mindegy',
            'class' => Diocese::class,
            'query_builder' => function (DioceseRepository $repository) {
                return $repository->createAllRecordQueryBuilder();
            },
            'choice_label' => 'name',
        ]);

        $builder->add('deanery', EntityType::class, [
            'label' => 'Esperes kerület',
            'required' => false,
            'placeholder' => 'mindegy',
            'class' => Deanery::class,
            'choice_label' => 'name',
            'choice_attr' => function (Deanery $deanery) {
                return [
                    'data-diocese-id' => $deanery->getDiocese(),
                ];
            },
        ]);

        $builder->add('only_greek', CheckboxType::class, [
            'label' => 'Csak görögkatolikus',
            'required' => false,
        ]);

        $builder->add('language', ChoiceType::class, [
            'label' => 'ahol van adott nyelvű mise is',
            'required' => false,
            'placeholder' => 'bármilyen',
            'choices' => $this->constantsProvider->getLanguageChoices(),
        ]);

        $builder->add('frequent_days', ChoiceType::class, [
            'label' => 'nap',
            'required' => true,
            'choices' => [
                'vasárnap' => 'sunday',
                'ma' => 'today',
                'holnap' => 'tomorrow',
                'adott napon:' => 'at',
            ],
        ]);

        $builder->add('mass_date', DateType::class, [
            'label' => null,
            'required' => false,
            'widget' => 'single_text',
        ]);

        $builder->add('time_of_day', ChoiceType::class, [
            'label' => 'nap',
            'required' => true,
            'choices' => [
                'délelőtt' => 'pm',
                'délután' => 'am',
                'adott időben' => 'at',
            ],
        ]);

        $builder->add('mass_time', TextType::class, [
            'label' => null,
            'required' => false,
        ]);

        $builder->add('music', ChoiceType::class, [
            'label' => 'zene',
            'required' => false,
            'expanded' => true,
            'multiple' => true,
            'choices' => [
                'csendes' => 'silent',
                'gitáros' => 'beat',
                'meghatározatlan' => 'na',
            ],
        ]);

        $builder->add('ages', ChoiceType::class, [
            'label' => 'korosztály',
            'required' => false,
            'expanded' => true,
            'multiple' => true,
            'choices' => [
                'ifjúsági/egyetemista' => 'youth',
                'diák' => 'secondary',
                'családos/mocorgós' => 'family',
                'meghatározatlan' => 'na',
            ],
        ]);

        $builder->add('rite', ChoiceType::class, [
            'label' => 'rítus',
            'required' => false,
            'placeholder' => 'mindegy',
            'choices' => [
                'görögkatolikus liturgia' => 'greek',
                'római katolikus szentmise' => 'roman',
                'régi rítusú szentmise' => 'trid',
            ],
        ]);

        $builder->add('liturgy', CheckboxType::class, [
            'label' => 'igeliturgiák is',
            'required' => false,
        ]);
    }
}
