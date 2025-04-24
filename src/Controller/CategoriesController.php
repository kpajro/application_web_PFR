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
    public function listProducts(int $id, ProduitRepository $produitRepository, CategorieRepository $categorieRepository, Request $request): Response
    {
        $products = $produitRepository->findByCategory($id);

        $categories = $categorieRepository->findAll();
        $formBuilder = $this->createFormBuilder(
            null,
            ['action' => $this->generateUrl('app_categorie', ['id' => $id])]
        );
        $formBuilder->add('prixMin', NumberType::class, [
                        'label' => 'Prix Min',
                        'required' => false,
                        'empty_data' => 0
                    ])
                    ->add('prixMax', NumberType::class, [
                        'label' => 'Prix Max',
                        'required' => true,
                        'data' => 10000,
                        'empty_data' => 10000
                    ])
                    ->add('ordreAlpha', ChoiceType::class, [
                        'label' => 'Ordre',
                        'choices' => [
                            'Prix' => 'prix',
                        ],
                        'multiple' => false,
                        'expanded' => false,
                        'placeholder' => 'Filtrez par',
                        'required' => false
                    ])
                    ->add('asc', ChoiceType::class, [
                        'label' => "asc/desc",
                        'choices' => [
                            'asc' => true,
                            'desc' => false,
                        ],
                        'multiple' => false,
                        'expanded' => false,
                        'placeholder' => '- - -',
                        'required' => false
                        ])
                    ->add('recherche', TextType::class, [
                        'required' => false
                    ])
        ;

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        return $this->render('categories/categorie.html.twig', [
            'produits' => $products,
            'categories' => $categories,
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
    public function productListInJson(Categorie $categorie, ProduitRepository $produitRepo, Request $request): JsonResponse
    {
        $filtres = json_decode($request->getContent(), true);

        $filtres = [
            'prix_minimum' => $filtres['prixMin'] ?? null,
            'prix_maximum' => $filtres['prixMax'] ?? null,
            'order' => $filtres['ordreAlpha'] ?? null,
            'asc' => $filtres['asc'] ?? null,
            'recherche' => $filtres['recherche'] ?? null
        ];

        $produits = $produitRepo->findByCategoryAndFilter($categorie, $filtres);
        return $this->json(['produits' => $produits]);
    }
}