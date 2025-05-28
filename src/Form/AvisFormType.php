<?php

namespace App\Form;

use App\Entity\Avis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class AvisFormType extends AbstractType 
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('note', NumberType::class, [
                'scale' => 1,
                'label' => 'Note',
                'label_attr' => ['class' => 'font-semibold text-xl'],
                'attr' => ['class' => 'max-w-[100px] text-xl text-center min-h-15']
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
                'label_attr' => ['class' => 'text-lg font-semibold'],
                'attr' => [
                    'class' => 'focus:shadow-lg shadow-indigo-300/30 text-gray-800 h-40',
                    'placeholder' => 'Faites part aux utilisateurs de ce que vous pensez de ce produit ! (1024 caractères max.)'
                ],
                'constraints' => [
                    new Length([
                        'max' => 1024,
                        'maxMessage' => '1024 caractères max.'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Avis::class
        ]);
    }
}