<?php

namespace App\Form;

use App\Entity\User;
use Faker\Provider\Text;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class, [
                'label'             => 'Nom',
                'required'          => true,

            ])
            ->add('firstName', TextType::class, [
                'label'             => 'Prénom',
                'required'          => true,

            ])
            ->add('email', EmailType::class, [
                'label'             => 'Adresse mail',
                'required'          => true,

            ])
            ->add('phone', TextType::class, [
                'label'             => 'Téléphone',
                'required'          => true,
            ])
            ->add('creationDate', DateType::class, [
                'label'             => false,
                'required'          => true,
                'data'              => new \DateTime('today'),
                'attr'              => [
                    'class'     => 'invisible'
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
