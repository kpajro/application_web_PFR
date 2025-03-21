<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class BOProduitFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'max' => 100
                    ])
                ],
                'row_attr' => ['class' => 'admin-form-section']
            ])
            ->add('editeur', TextType::class, [
                'label' => 'Edité par',
                'row_attr' => ['class' => 'admin-form-section']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du produit',
                'constraints' => [],
                'required' => false,
                'row_attr' => ['class' => 'admin-form-section']
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Prix',
                'constraints' => [],
                'row_attr' => ['class' => 'admin-form-section']
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'label' => 'Catégorie du produit',
                'row_attr' => ['class' => 'admin-form-section']
            ])
            ->add('image', FileType::class, [
                'label' => 'Image du produit',
                'required' => false,
                'mapped' => false,
                'row_attr' => ['class' => 'admin-form-section']
            ])
            ->add('os', ChoiceType::class, [
                'choices' => [
                    'Windows' => 'WIN',
                    'Linux' => 'LINUX',
                    'MacOS' => 'MACOS'
                ],
                'label' => 'Disponible sur les plateformes',
                'expanded' => true,
                'multiple' => true,
                'row_attr' => ['class' => 'admin-form-section'],
                'attr' => ['class' => 'admin-form-checks']
            ])
            ->add('langages', ChoiceType::class, [
                'choices' => [
                    'Français' => 'FR',
                    'Anglais' => 'EN',
                    'Allemand' => 'GER'
                ],
                'label' => 'Disponible en',
                'expanded' => true,
                'multiple' => true,
                'row_attr' => ['class' => 'admin-form-section'],
                'attr' => ['class' => 'admin-form-checks']
            ])
            ->add('isLimitedStock', CheckboxType::class, [
                'label' => 'Le stock est limité',
                'required' => false,
                'row_attr' => ['class' => 'admin-form-section'],
                'attr' => ['class' => 'admin-form-checks']
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Limite de vente',
                'required' => false,
                'row_attr' => ['class' => 'admin-form-section']
            ])
            ->add('isBulkSale', CheckboxType::class, [
                'label' => 'La vente se fait par lot',
                'required' => false,
                'row_attr' => ['class' => 'admin-form-section'],
                'attr' => ['class' => 'admin-form-checks']
            ])
            ->add('bulkSize', IntegerType::class, [
                'label' => 'Taille des lots',
                'required' => false,
                'row_attr' => ['class' => 'admin-form-section']
            ])
            ->add('longDescription', TextareaType::class, [
                'label' => 'Déscription détaillée du produit',
                'required' => false,
                'row_attr' => ['class' => 'admin-form-section']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}