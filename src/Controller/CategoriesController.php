<?php
namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}