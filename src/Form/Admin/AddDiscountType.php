<?php

namespace App\Form\Admin;

use App\Entity\Discount;
use DateTime;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddDiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label'     => 'Nom',
                'required'  => true,
                'attr'      => [
                    'maxlength' => 155
                ]
            ])
            ->add('percentage', NumberType::class, [
                'label'     => 'Pourcentage',
                'required'  => true,
                'attr'      => [
                    'min'       => 1,
                    'max'       => 99
                ]
            ])
            ->add('startingDate', DateType::class, [
                'label'     => 'Date de début',
                'required'  => true,
                'widget'    => 'single_text',
                'attr'      => [
                    'min'       => (new \DateTime('now'))->format('Y-m-d')
                ]
            ])
            ->add('endingDate', DateType::class, [
                'label'     => 'Date de fin',
                'required'  => true,
                'widget'    => 'single_text',
                'attr'      => [
                    'min'       => (new DateTime('now'))->format('Y-m-d')
                ]
            ])
            ->add('brand', ChoiceType::class, [
                'label'         => 'Marque',
                'required'      => false,
                'mapped'        => false,
                'choices'       => $options['brands'],
            ])
            ->add('product', ChoiceType::class, [
                'label'         => 'Produit',
                'required'      => false,
                'mapped'        => false,
                'disabled'      => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => Discount::class,
            'products'      => array(),
            'brands'        => array()
        ]);
    }
}
