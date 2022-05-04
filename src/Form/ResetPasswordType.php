<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type'              => PasswordType::class,
                'invalid_message'   => 'Les mots de passe ne correspondent pas.',
                'options'           => ['attr' => ['class' => '']],
                'required'          => true,
                'first_options'     => ['label' => 'Nouveau mot de passe'],
                'second_options'    => ['label' => 'Confirmez le nouveau mot de passe'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
