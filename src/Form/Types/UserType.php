<?php

namespace App\Form\Types;

use PhpParser\Node\Expr\BinaryOp\NotEqual;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', TextType::class, [
            'label' => 'Felhasználói név',
            'help' => 'Ékezet és speciális karakterek nélkül, maximum 20 betű. Szóköz, idézőjel és aposztróf NEM lehet benne!'
                .' Ez a név azonosít, ezzel tudsz majd belépni, de alább lehetőség van külön becenév megadására is.',
            'constraints' => [
                new NotBlank(message: 'A mezőt kötelező kitölteni!'),
                new Length(max: 20),
                new Regex(pattern: '[\w0-9]*')
            ]
        ]);

        $builder->add('nickname', TextType::class, [
            'label' => 'Becenév, megszólítás',
            'help' => 'Ide keresztnevet, vagy becenevet célszerű írni. Alapvetően ezen a néven jelensz meg oldalunkon, '
                .'az azonosításhoz mellette kicsiben jelezzük a bejelentkezési neved is.',
            'constraints' => [
                new NotBlank(message: 'A mezőt kötelező kitölteni!'),
                new Length(max: 1), // mennyi a max?
            ]
        ]);

        $builder->add('name', TextType::class, [
            'label' => 'Név',
            'help' => 'Teljes név. Haszon pl. észrevétel vagy adatmódosítés beküldése esetén, hogy a szereksztők könnyebben azonosíthassák a beküldőt.',
            'constraints' => [
                new NotBlank(message: 'A mezőt kötelező kitölteni!'),
                new Length(max: 1), // mennyi a max?
            ]
        ]);

        $builder->add('email', TextType::class, [
            'label' => 'Email',
            'help' => 'A regisztrációhoz szükséges egy valós emailcím! Erre a címre küldjük ki az ideiglenes jelszót. Elküldés előtt kérjük ellenőrizd!',
            'constraints' => [
                new NotBlank(message: 'A mezőt kötelező kitölteni!'),
                new Length(max: 1), // mennyi a max?
            ]
        ]);

        $user = $options['data'] ?? null;

        if ($user === null) {
            $termsUrl = $this->urlGenerator->generate('terms_and_conditions');

            $builder->add('terms', CheckboxType::class, [
                'label' => 'Elfogadom a <a href="'.$termsUrl.'" target="_blank">Miserend házirendjét</a> és vállalom, hogy mindenben betartom!',
                'label_html' => true,
                'constraints' => [
                    new NotBlank(message: 'A mezőt kötelező kitölteni!'),
                ]
            ]);

            $builder->add('question', TextType::class, [
                'label' => 'Mi a Magyar Katolikus Püspöki Konferencia négy betűs rövidítése?',
                'help' => 'Sajnos automata robotok is folyton regisztrálnak és ellenük kell ilyen kérdést feltennünk.',
                'constraints' => [
                    new NotBlank(message: 'A mezőt kötelező kitölteni!'),
                    new NotEqualTo(value: 'MKPK', message: '')
                ]
            ]);
        } else {

            $builder->add('notifications', CheckboxType::class, [
                'label' => 'Email értesítések',
                'required' => false,
                'help' => 'Leginkább a felelőssségi köreidbe tartozó templomokhoz érkező észrevételekről küldünk üzeneteket.',
            ]);

            $builder->add('notifications', ChoiceType::class, [
                'label' => 'Jogosultságok',
                'required' => false,
                'choices' => [
                    'user' => 'user',
                    'miserend' => 'miserend',
                ],
            ]);

        }
    }
}