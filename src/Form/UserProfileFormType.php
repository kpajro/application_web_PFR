<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class UserProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail *',
                'constraints' => [
                    new Length([
                        'max' => 100,
                        'min' => 5,
                        'minMessage' => 'Votre adresse e-mail doit contenir au minimum 5 caractères.',
                        'maxMessage' => 'Votre adresse e-mail ne peut pas contenir plus de 100 caractères.'
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}.*$/',        // regex pour la validité de l'adresse mail 
                        'match' => true,
                        'message' => 'L\'adresse e-mail doit finir par un nom de domaine valide (".fr", ".com", ".net", etc.).'
                    ])
                ],
                'label_attr' => ['font-medium']
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Length([
                        'max' => 50,
                        'min' => 2,
                        'minMessage' => 'Le nom de famille doit contenir au minimum 2 caractères.',
                        'minMessage' => 'Le nom de famille ne peut pas contenir plus de 50 caractères.'
                    ])
                ],
                'label_attr' => ['font-medium']
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new Length([
                        'max' => 50,
                        'min' => 2,
                        'minMessage' => 'Le prénom doit contenir au minimum 2 caractères.',
                        'minMessage' => 'Le prénom ne peut pas contenir plus de 50 caractères.'
                    ])
                ],
                'label_attr' => ['font-medium']
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
                'placeholder' => 'Séléctionnez votre pays de résidence.',
                'label_attr' => ['font-medium'],
                'required' => false
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Numéro de téléphone',
                'constraints' => [
                    new Length([
                        'max' => 15,
                        'min' => 10,
                        'minMessage' => 'Votre numéro de téléphone doit contenir au minimum 10 caractères.',
                        'maxMessage' => 'Votre numéro de téléphone ne peut contenir pas plus de 15 caractères.'
                    ]),
                    new Regex([
                        'pattern' => '/^\+?[0-9\s]+$/',     // regex pour la validité du numéro de téléphone
                        'match' => true,
                        'message' => 'Le numéro de téléphone ne peut contenir que des chiffres, des espaces et le caractère "+".'
                    ])
                    ],
                'label_attr' => ['font-medium'],
                'required' => false
            ])
            ->add('birthday', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'label' => 'Date de naissance',
                'required' => false
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