<?php
namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use ContainerKKo5JGl\getProduitControllerviewProduitService;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use function PHPUnit\Framework\isInfinite;

class CategoriesController extends AbstractController
{
    #[Route('/categories', name:'app_categories_list')]
    public function categoriesList(CategorieRepository $categoriesRepo): Response
    {
        $categories = $categoriesRepo->findAll();

        return $this->render('categories/list.html.twig', [
            'categories' => $categories,
        ]);
    }
    
    #[Route('/{id}/categorie', name: 'app_categorie')]
    public function listProducts(Categorie $categorie, ProduitRepository $produitRepository, Request $request): Response
    {
        $products = $produitRepository->findByCategory($categorie->getId());
        $formBuilder = $this->createFormBuilder(
            null,
            ['action' => $this->generateUrl('app_categorie', ['id' => $categorie->getId()])]
        );
        $formBuilder->add('prixMin', NumberType::class, [
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
                            'Alphabétique' => 'alpha'
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
                        'label' => "OS",
                        'choices' => [
                            'WIN' => "WIN",
                            'LIN' => "LIN",
                            'MACOS' => "MacOS"
                        ],
                        'multiple' => false,
                        'expanded' => false,
                        'placeholder' => "Choisissez l'OS",
                        'required' => false
                    ])
                    ->add('langages', ChoiceType::class, [
                        'label' => "Languages",
                        'choices' => [
                            'French' => 'FR',
                            'English' => 'EN',
                            'Italian' => 'ITA',
                            'German' => 'GER',
                            'Spanish' => 'SPA'
                        ],
                        'multiple' => false,
                        'expanded' => false,
                        'placeholder' => "Choisissez la langue",
                        'required' => false
                    ])
                    ->add('editor', ChoiceType::class, [
                        'label' => "Editeurs",
                        'choices' => [
                            
                        ],
                        'multiple' => false,
                        'expanded' => false,
                        'placeholder' => "Choisissez l'éditeur",
                        'required' => false
                    ])
                    ->add('recherche', TextType::class, [
                        'required' => false,
                        'label' => 'Rechercher',
                        'label_attr' => ['class' => 'text-xs italic text-gray-700 text-end'],
                        'attr' => ['class' => 'filtre-input filtre-search', 'placeholder' => "Entrez le nom d'un produit"],
                        'row_attr' => ['class' => 'flex flex-col justify-center'],
                        
                    ])
        ;

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        return $this->render('categories/categorie.html.twig', [
            'produits' => $products,
            'categorie' => $categorie,
            'filterForm' => $form->createView()
        ]);
    }
    
    /*#[Route('/{id}/categorie/json', name: 'app_categorie_json', methods: ['GET'])]
    public function listProductsToJson(int $id, ProduitRepository $produitRepository, CategorieRepository $categorieRepository, Request $request): JsonResponse 
    {
        $filtre = $request->query->get('filtre', 'default');
        $products = $produitRepository->findByCategory($id, $filtre);

        $categories = $categorieRepository->findAll();

        $produitsArray = array_map(function($prod){
            return [
                'id'=> $prod->getId(),
                'nom'=> $prod->getNom(),
                'description'=> $prod->getDescription(),
                'prix'=> $prod->getPrix(),
                'image'=> $prod->getImage(),
                'categorie'=> [
                    'id'=> $prod->getCategorie()->getId(),
                    'nom'=> $prod->getCategorie()->getNom()
                ]
            ];
        }, $products);
        $categoriesArray = array_map(function($categ){
            return [
                'id'=> $categ->getId(),
                'nom'=> $categ->getNom(),
                'nbProduits'=> $categ->getNbProduits()
            ];
        }, $categories);
        return $this->json([
            'produits'=> $produitsArray,
            'filtre'=> $filtre,
            'categories'=> $categoriesArray
        ]);
    }*/

    #[Route('/categorie/{id}/produits/list', name: 'app_categorie_produits_json', methods: ['POST'])]
    public function productListInJson(Categorie $categorie, ProduitRepository $produitRepo, Request $request, SluggerInterface $slugger, LoggerInterface $logger): JsonResponse

    {
        $filtres = json_decode($request->getContent(), true);
        $filtres = [
            'prix_minimum' => $filtres['prixMin'] ?? null,
            'prix_maximum' => $filtres['prixMax'] ?? null,
            'order' => $filtres['ordreAlpha'] ?? null,
            'asc' => $filtres['asc'] ?? null,
            'recherche' => $filtres['recherche'] ?? null,
            'os' => $filtres['os'] ?? null,
            //'langages' => $filtres['langages'] ?? null,
            'editor' => $filtres['editor'] ?? null
        ];
        $logger->info('Debug info:', ['data' => $filtres]);

        $produits = $produitRepo->findByCategoryAndFilter($categorie, $filtres);
        return $this->json([
            'produits' => $produits,
            'categorie' => $categorie->getNom(),
            'directory' => 'uploadedFiles/produitImages/' . $slugger->slug($categorie->getNom())
        ]);
    }
}