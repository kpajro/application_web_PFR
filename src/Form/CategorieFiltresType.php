<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType as TypeTextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorieFiltresType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prixMin', NumberType::class, [
                'label' => 'Prix Minimum',
                'required' => false,
                'empty_data' => 0,
                'label_attr' => ['class' => 'text-xs italic text-gray-700 text-end'],
                'attr' => ['class' => 'filtre-input'],
                'row_attr' => ['class' => 'flex flex-col justify-center']
            ])
            ->add('prixMax', NumberType::class, [
                'label' => 'Prix Maximum',
                'required' => true,
                'data' => 10000,
                'empty_data' => 10000,
                'label_attr' => ['class' => 'text-xs italic text-gray-700 text-end'],
                'attr' => ['class' => 'filtre-input'],
                'row_attr' => ['class' => 'flex flex-col justify-center']
            ])
            ->add('ordreAlpha', ChoiceType::class, [
                'label' => 'Filtrer par',
                'choices' => [
                    'Prix' => 'prix',
                    'Alphabétique' => 'alpha',
                    'Note' => 'note'
                ],
                'multiple' => false,
                'expanded' => false,
                'placeholder' => 'Défaut',
                'required' => false,
                'label_attr' => ['class' => 'text-xs italic text-gray-700 text-end'],
                'attr' => ['class' => 'filtre-input'],
                'row_attr' => ['class' => 'flex flex-col justify-center']
            ])
            ->add('asc', ChoiceType::class, [
                'label' => "Ordre",
                'choices' => [
                    'Croissant' => true,
                    'Décroissant' => false,
                ],
                'multiple' => false,
                'expanded' => false,
                'required' => false,
                'label_attr' => ['class' => 'text-xs italic text-gray-700 text-end'],
                'attr' => ['class' => 'filtre-input'],
                'row_attr' => ['class' => 'flex flex-col justify-center']
            ])
            ->add('os', ChoiceType::class, [
                'label' => "Systèmes d'exploitation disponibles",
                'choices' => [
                    'Windows' => "WIN",
                    'Linux' => "LIN",
                    'MacOS' => "MacOS"
                ],
                'multiple' => true,
                'expanded' => true,
                'placeholder' => "Tous",
                'required' => false,
                'label_attr' => ['class' => 'text-xs italic text-gray-700 text-end'],
                'attr' => ['class' => 'filtre-boxes'],
                'row_attr' => ['class' => 'flex flex-col justify-center']
            ])
            ->add('langages', ChoiceType::class, [
                'label' => "Langages disponibles",
                'choices' => [
                    'Français' => 'FR',
                    'Anglais' => 'EN',
                    'Italien' => 'ITA',
                    'Allemand' => 'GER',
                    'Espagnol' => 'SPA'
                ],
                'multiple' => true,
                'expanded' => true,
                'placeholder' => "Toutes les langues",
                'required' => false,
                'label_attr' => ['class' => 'text-xs italic text-gray-700 text-end'],
                'attr' => ['class' => 'filtre-boxes'],
                'row_attr' => ['class' => 'flex flex-col justify-center']
            ])
            ->add('editor', ChoiceType::class, [
                'label' => "Editeurs",
                'choices' => [
                    
                ],
                'multiple' => true,
                'expanded' => true,
                'placeholder' => "Tous",
                'required' => false,
                'label_attr' => ['class' => 'text-xs italic text-gray-700 text-end'],
                'attr' => ['class' => 'filtre-boxes'],
                'row_attr' => ['class' => 'flex flex-col justify-center']
            ])
            ->add('recherche', TypeTextType::class, [
                'required' => false,
                'label' => false,
                'label_attr' => ['class' => 'text-xs italic text-gray-700 text-end'],
                'attr' => ['class' => 'filtre-input filtre-search', 'placeholder' => "Recherchez un produit..."],
                'row_attr' => ['class' => 'flex flex-col justify-center w-full'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}