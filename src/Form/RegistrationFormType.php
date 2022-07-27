<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class, [
                'label'             => 'Nom',
                'required'          => true,
                'attr'              => [
                    'maxlength' => 33
                ]
            ])
            ->add('firstName', TextType::class, [
                'label'             => 'Prénom',
                'required'          => true,
                'attr'              => [
                    'maxlength' => 33
                ]
            ])
            ->add('email', EmailType::class, [
                'label'             => 'Adresse mail',
                'required'          => true
            ])
            ->add('phone', TextType::class, [
                'label'             => 'Téléphone',
                'required'          => true,
                'attr'              => [
                    'maxlength' => 10
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type'              => PasswordType::class,
                'invalid_message'   => 'Les mots de passe ne correspondent pas.',
                'options'           => ['attr' => ['class' => '']],
                'required'          => true,
                'first_options'     => ['label' => 'Mot de passe'],
                'second_options'    => ['label' => 'Confirmez le mot de passe'],
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
