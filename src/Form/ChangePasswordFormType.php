<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainOldPassword', PasswordType::class, [
                'label' => "Mot de passe actuel",
                'label_attr' => ['font-medium mb-2'],
            ])
            ->add('plainNewPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options'  => ['label' => 'Nouveau mot de passe'],
                'second_options' => ['label' => 'Confirmez le mot de passe'],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un mot de passe.']),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d;:*\\\\\/{}]).{8,}$/',     // regex pour mdp robuste : 6 caractères minimum, une maj, une  min, un chiffre et un caractère spécial (en excluant ceux qui peuvent être dangereux)
                        'match' => true,
                        'message' => 'Le mot de passe doit contenir au minimum 6 caractères, une minuscule, une majuscule, un chiffre et un caractère spécial.'
                    ])
                ],
                'label_attr' => ['font-medium mb-2'],
                'attr' => ['class' => 'mb-3'],
            ])
        ;
    }
}
