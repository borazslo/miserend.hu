<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Types;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('_username', TextType::class, [
            'label' => 'Felhasználónév',
            'required' => false,
        ]);
        $builder->add('_password', PasswordType::class, [
            'label' => 'Jelszó',
            'required' => false,
        ]);
        $builder->add('_remember_me', CheckboxType::class, [
            'label' => 'Megjegyzés',
            'required' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
