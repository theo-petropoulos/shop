<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ModifyPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isAdminEdit    = $options['isAdminEdit'] ?? null;
        $adminRoute     = $options['adminRoute'] ?? null;

        if ($isAdminEdit && $adminRoute)
            $builder->setAction($adminRoute);

        $builder
            ->add('old_password', PasswordType::class, [
                'mapped'            => false,
                'label'             => 'Ancien mot de passe',
                'required'          => true
            ])
            ->add('password', RepeatedType::class, [
                'type'              => PasswordType::class,
                'invalid_message'   => 'Les mots de passe ne correspondent pas.',
                'options'           => ['attr' => ['class' => '']],
                'required'          => true,
                'first_options'     => ['label' => 'Nouveau mot de passe'],
                'second_options'    => ['label' => 'Confirmez le nouveau mot de passe'],
                'constraints'       => [
                    new Assert\Regex(
                        '"^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"',
                        'Le mot de passe doit contenir au moins 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre et un caractère spécial.'
                    )
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => User::class,
            'isAdminEdit'   => false,
            'adminRoute'    => null
        ]);
    }
}
