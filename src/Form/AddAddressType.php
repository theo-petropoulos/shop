<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class AddAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class, [
                'label'     => 'Nom',
                'required'  => true,
                'attr'      => [
                    'maxlength' => 155
                ]
            ])
            ->add('firstName', TextType::class, [
                'label'     => 'Prénom',
                'required'  => true,
                'attr'      => [
                    'maxlength' => 33
                ]
            ])
            ->add('streetNumber', TextType::class, [
                'label'     => 'Numéro de la rue',
                'required'  => false,
                'attr'      => [
                    'maxlength' => 10
                ]
            ])
            ->add('streetName', TextType::class, [
                'label'     => 'Nom de la rue',
                'required'  => true,
                'attr'      => [
                    'maxlength' => 255
                ]
            ])
            ->add('streetAddition', TextType::class, [
                'label'     => 'Complément d\'adresse',
                'required'  => false,
                'attr'      => [
                    'maxlength' => 255
                ]
            ])
            ->add('postalCode', NumberType::class, [
                'label'     => 'Code postal',
                'required'  => true,
                'attr'      => [
                    'minlength' => 4,
                    'maxlength' => 5
                ]
            ])
            ->add('city', TextType::class, [
                'label'     => 'Ville',
                'required'  => true,
                'attr'      => [
                    'minlenght' => 2,
                    'maxlength' => 80
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
