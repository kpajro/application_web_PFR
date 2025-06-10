<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('name', TextType::class)
            ->add('firstname', TextType::class)
            ->add('country', TextType::class)
            ->add('phoneNumber', TextType::class)
            ->add('birthday', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ])
                
            ->add('currentPassword', PasswordType::class, [
            'mapped' => false,
            'required' => false,
            'label' => 'Mot de passe actuel',
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'first_options'  => ['label' => 'Nouveau mot de passe'],
                'second_options' => ['label' => 'Confirmation du nouveau mot de passe'],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d;:*\\\\\/{}]).{8,}$/',
                        'match' => true,
                        'message' => 'Le mot de passe doit contenir au minimum 8 caractères, une minuscule, une majuscule, un chiffre et un caractère spécial.'
                    ])
                ]
            ])
            
            ->add('confirmPassword', PasswordType::class, [
            'mapped' => false,
            'required' => false,
            'label' => 'Confirmer le nouveau mot de passe',
            ])

            ->add('billingAddress', TextType::class, [
            'required' => false,
            'label' => 'Adresse de facturation',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}