<?php

namespace App\Form\Admin;

use App\Entity\Brand;
use App\Entity\Discount;
use App\Entity\Product;
use App\Repository\DiscountRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('brand', EntityType::class, [
                'label'         => 'Marque',
                'required'      => true,
                'class'         => Brand::class,
                'choice_label'  => 'name'
            ])
            ->add('discount', EntityType::class, [
                'label'         => 'Promotion',
                'required'      => false,
                'class'         => Discount::class,
                'query_builder' => function (DiscountRepository $discountRepository) {
                    return
                        $discountRepository
                            ->createQueryBuilder('d')
                            ->orderBy('d.percentage', 'ASC');
                },
                'choice_label'  => 'fullDiscount'
            ])
            ->add('name', TextType::class, [
                'label'     => 'Nom',
                'required'  => true,
                'attr'      => [
                    'maxlength' => 155
                ]
            ])
            ->add('description', TextareaType::class, [
                'label'     => 'Description',
                'required'  => true,
                'attr'      => [
                    'max'       => 500
                ]
            ])
            ->add('price', NumberType::class, [
                'label'     => 'Prix',
                'required'  => true
            ])
            ->add('stock', NumberType::class, [
                'label'     => 'Stock',
                'required'  => true
            ])
            ->add('active', CheckboxType::class, [
                'label'     => 'Activer',
                'required'  => false
            ])
            ->add('images', FileType::class, [
                'label'     => 'Images',
                'required'  => false,
                'multiple'  => true,
                'mapped'    => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
