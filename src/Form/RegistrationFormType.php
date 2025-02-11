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
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'constraints' => [
                    new Length([
                        'max' => 100,
                        'min' => 5,
                        'message' => 'L\'adresse e-mail doit contenir entre 5 et 100 caractères.'
                    ]),
                    new Regex([
                        'pattern' => '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}.*$',
                        'match' => true,
                        'message' => 'L\'adresse e-mail doit finir par un nom de domaine valide (".fr", ".com", ".net", etc.).'
                    ])
                ]
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new Length([
                        'max' => 50,
                        'min' => 2,
                        'message' => 'Le prénom doit contenir entre 2 et 50 caractères.'
                    ])
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Length([
                        'max' => 50,
                        'min' => 2,
                        'message' => 'Le nom de famille doit contenir entre 2 et 50 caractères.'
                    ])
                ]
            ])
            ->add('birthday', DateType::class, [
                'label' => 'Date de naissance',
                'constraints' => [
                ]
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Numéro de téléphone',
                'constraints' => [
                    new Length([
                        'max' => 15,
                        'min' => 10,
                        'message' => 'Votre numéro de téléphone doit contenir 10 et 15 caractères.'
                    ]),
                    new Regex([
                        'pattern' => '^\+?[0-9\s]+$',
                        'match' => true,
                        'message' => 'Le numéro de téléphone ne peut contenir que des chiffres, des éspaces et le caractère "+".'
                    ])
                ]
            ])
            ->add('country', ChoiceType::class, [
                'label' => 'Pays de résidence',
                'choices' => [
                    'France' => 'FR',
                    'Royaume Uni' => 'UK',
                    'Etats Unis' => 'USA',
                    'Italie' => 'IT',
                ],
                'multiple' => false,
                'expanded' => false,
                'placeholder' => 'Séléctionnez votre pays de résidence.'
            ])
            ->add('accountType', ChoiceType::class, [
                'label' => 'Type de compte',
                'choices' => [
                    'Compte pour entreprise' => 1,
                    'Compte pour particulier' => 2
                ],
                'multiple' => false,
                'expanded' => true
            ])
            ->add('plainPassword', RepeatedType::class, [
                'first_options' => [
                    'label' => 'Mot de passe'
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
                        'pattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d;:*\\\/{}]).{8,}$',
                        'match' => true,
                        'message' => 'Le mot de passe doit contenir au minimum 6 caractères, une minuscules, une majuscule, un chiffre et un caractère spécial.'
                    ])
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => "J'accepte les conditions d'utilisation du site",
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
