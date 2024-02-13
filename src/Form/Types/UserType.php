<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Types;

use App\Entity\User;
use App\Form\ChoiceLoaders\RoleChoiceLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RoleChoiceLoader $roleChoiceLoader,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', User::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('username', TextType::class, [
            'label' => 'Felhasználói név',
            'help' => 'Ékezet és speciális karakterek nélkül, maximum 20 betű. Szóköz, idézőjel és aposztróf NEM lehet benne!'
                .' Ez a név azonosít, ezzel tudsz majd belépni, de alább lehetőség van külön becenév megadására is.',
            'required' => false,
            'constraints' => [
                new NotBlank(message: 'A mezőt kötelező kitölteni!'),
                new Length(max: 20),
                new Regex(pattern: '/^[\w0-9]*$/', message: 'Érvénytelen karaktert tartalmaz!'),
            ],
        ]);

        $builder->add('nickname', TextType::class, [
            'label' => 'Becenév, megszólítás',
            'help' => 'Ide keresztnevet, vagy becenevet célszerű írni. Alapvetően ezen a néven jelensz meg oldalunkon, '
                .'az azonosításhoz mellette kicsiben jelezzük a bejelentkezési neved is.',
            'required' => false,
            'constraints' => [
                new NotBlank(message: 'A mezőt kötelező kitölteni!'),
                new Length(max: 50),
            ],
        ]);

        $builder->add('fullName', TextType::class, [
            'label' => 'Név',
            'help' => 'Teljes név. Haszon pl. észrevétel vagy adatmódosítés beküldése esetén, hogy a szereksztők könnyebben azonosíthassák a beküldőt.',
            'required' => false,
            'constraints' => [
                new NotBlank(message: 'A mezőt kötelező kitölteni!'),
                new Length(max: 100),
            ],
        ]);

        $builder->add('email', TextType::class, [
            'label' => 'Email',
            'help' => 'A fiókodhoz szükséges egy valós emailcím! Erre a címre néha küldünk fontos levelet. Elküldés előtt kérjük ellenőrizd!',
            'required' => false,
            'constraints' => [
                new NotBlank(message: 'A mezőt kötelező kitölteni!'),
                new Length(max: 100),
            ],
        ]);

        $user = $options['data'];
        \assert($user === null || $user instanceof User);

        if ($user->getId() === null) {
            $termsUrl = $this->urlGenerator->generate('terms_and_conditions');

            $builder->add('terms', CheckboxType::class, [
                'label' => 'Elfogadom a <a href="'.$termsUrl.'" target="_blank">Miserend házirendjét</a> és vállalom, hogy mindenben betartom!',
                'label_html' => true,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(message: 'A házirendet kötelező elfogadni (és elolvasni)!'),
                ],
            ]);

            $builder->add('question', TextType::class, [
                'label' => 'Mi a Magyar Katolikus Püspöki Konferencia négy betűs rövidítése?',
                'mapped' => false,
                'help' => 'Sajnos automata robotok is folyton regisztrálnak és ellenük kell ilyen kérdést feltennünk.',
                'required' => false,
                'constraints' => [
                    new NotBlank(message: 'A mezőt kötelező kitölteni!'),
                    new EqualTo(value: 'MKPK', message: 'Sajnos a válasz nem jó :('),
                ],
            ]);
        } else {
            $builder->add('notifications', CheckboxType::class, [
                'label' => 'Email értesítések',
                'required' => false,
                'help' => 'Leginkább a felelőssségi köreidbe tartozó templomokhoz érkező észrevételekről küldünk üzeneteket.',
            ]);

            $builder->add('roles', ChoiceType::class, [
                'label' => 'Jogosultságok',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choice_loader' => ChoiceList::loader($this, $this->roleChoiceLoader, $user->getRoles()),
            ]);
        }
    }

    public function getBlockPrefix(): string
    {
        return 'user';
    }
}
