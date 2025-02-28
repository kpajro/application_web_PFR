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
    // j'aurais besoin d'un coup de main la prochaine fois @leosu ~#Klaudiusz
    #[Route('/', name: 'liste_categories')]
    public function listeCategories(CategorieRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findAll();

        return $this->render('index/index.html.twig', [
            'categories' => $categories,
        ]);
    }
    #[Route('/categorie/{id}', name: 'produits_categorie')]
    public function listProducts(int $id, ProduitRepository $produitRepository, CategorieRepository $categorieRepository, Request $request): Response
    {
        $filtre = $request->query->get('filtre', 'default');
        $products = $produitRepository->findByCategory($id, $filtre);

        $categories = $categorieRepository->findAll();

        return $this->render('pages/categories/categories.html.twig', [
            'produits' => $products,
            'filtre' => $filtre,
            'categories' => $categories
        ]);
    }
}