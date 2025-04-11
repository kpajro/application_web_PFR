<?php
namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use ContainerKKo5JGl\getProduitControllerviewProduitService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $filtre = $request->query->get('filtre', 'default');
        $products = $produitRepository->findByCategory($id, $filtre);

        $categories = $categorieRepository->findAll();

        return $this->render('categories/categorie.html.twig', [
            'produits' => $products,
            'filtre' => $filtre,
            'categories' => $categories
        ]);
    }
    
    // ~#Klaudiusz -> on peux faire mieux ? ~@leosu1
    #[Route('/{id}/categorie/json', name: 'app_categorie_json', methods: ['GET'])]
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
    }


    // ce que je ferais #léo @klaudiusz
    #[Route('/categorie/{id}/produits/list', name: 'app_categorie_produits_json')]
    public function productListInJson(Categorie $categorie, ProduitRepository $produitRepo, Request $request): JsonResponse
    {
        $filtres = [
            'prix_minimum' => $request->get('prixMin'),
            'prix_maximum' => $request->get('prixMax'),
            'order' => $request->get('orderBy'),
            'asc' => $request->get('asc')
        ];

        $produits = $produitRepo->findByCategoryAndFilter($categorie, $filtres);
        return $this->json(['produits' => $produits]);
    }
}