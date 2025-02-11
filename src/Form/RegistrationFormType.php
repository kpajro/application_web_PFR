<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [])
            ->add('firstname', TextType::class, [])
            ->add('name', TextType::class, [])
            ->add('birthday', DateType::class, [])
            ->add('phoneNumber', TextType::class, [])
            ->add('country', ChoiceType::class, [
                'choices' => [
                    'France' => 'FR',
                    'United Kingdom' => 'UK',
                    'United States' => 'USA',
                    'Italia' => 'IT',
                ],
                'multiple' => false,
                'expanded' => false,
                'placeholder' => 'Séléctionnez votre pays de résidence.'
            ])
            ->add('accountType', ChoiceType::class, [
                'choices' => [
                    'Compte pour entreprise' => 1,
                    'Compte pour particulier' => 2
                ],
                'multiple' => false,
                'expanded' => true
            ])
            ->add('plainPassword', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'type' => PasswordType::class,
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe.',
                    ]),
                    new Regex([
                        'pattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d;:*\\\/{}]).{8,}$',
                        'match' => true,
                        'message' => 'Le mot de passe doit contenir au minimum 6 caractères, une minuscules, une majuscule, un chiffre et un caractère spécial.'
                    ])
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Merci d\'accepter les conditions d\'utilisation pour continuer.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
