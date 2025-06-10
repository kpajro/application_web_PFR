<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\Regex;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainOldPassword', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe.',
                    ]),
                ],
                'label_attr' => ['font-semibold'],
                'mapped' => false
            ])
            ->add('plainNewPassword', RepeatedType::class, [
                'first_options' => [
                    'label' => 'Nouveau mot de passe'
                ],
                'second_options' => [
                    'label' => 'Je confirme le mot de passe'
                ],
                'type' => PasswordType::class,
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe.',
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d;:*\\\\\/{}]).{8,}$/',
                        'match' => true,
                        'message' => 'Le mot de passe doit contenir au minimum 6 caractères, une minuscule, une majuscule, un chiffre et un caractère spécial.'
                    ]),
                    new NotCompromisedPassword()
                ],
                'label_attr' => ['font-semibold']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
