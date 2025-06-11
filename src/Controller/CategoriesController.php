<?php
namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieFiltresType;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoriesController extends AbstractController
{
    /**
     * Route pour l'affichage de la liste des catégories : '/categories' ('app_categories_list')
     * @return Response Template twig 'categories/list.html.twig'
     */
    #[Route('/categories', name:'app_categories_list')]
    public function categoriesList(CategorieRepository $categoriesRepo): Response
    {
        $categories = $categoriesRepo->findAll();

        return $this->render('categories/list.html.twig', [
            'categories' => $categories,
        ]);
    }
    
    /**
     * Route qui liste les produits d'une catégorie ciblée : '/{id}/categorie' ('app_categorie')
     * @return Response Template twig 'categories/categorie.html.twig'
     */
    #[Route('/{id}/categorie', name: 'app_categorie')]
    public function listProducts(Categorie $categorie, ProduitRepository $produitRepository, Request $request): Response
    {
        $products = $produitRepository->findByCategory($categorie->getId());

        // création d'un formulaire pour les filtres
        $form = $this->createForm(CategorieFiltresType::class, null, [
            'action' => $this->generateUrl('app_categorie', ['id' => $categorie->getId()])
        ]);
        $form->handleRequest($request);

        return $this->render('categories/categorie.html.twig', [
            'produits' => $products,
            'categorie' => $categorie,
            'filterForm' => $form->createView()
        ]);
    }

    /**
     * Route qui renvoie les produits d'une categorie selon les filtres
     * @param Categorie $categorie Categorie ciblée par le chemin de la route
     * @return JsonResponse Json contenant tous les produits selon les filtres envoyés
     */
    #[Route('/categorie/{id}/produits/list', name: 'app_categorie_produits_json', methods: ['POST'])]
    public function productListInJson(Categorie $categorie, ProduitRepository $produitRepo, Request $request, SluggerInterface $slugger, LoggerInterface $logger): JsonResponse
    {
        // on reçoit les filtres au format JSON depuis le front (categorie_controller)
        $filtres = json_decode($request->getContent(), true);
        // Reformattage de l'array reçu
        $filtres = [
            'prix_minimum' => $filtres["categorie_filtres[prixMin]"] ?? null,
            'prix_maximum' => $filtres["categorie_filtres[prixMax]"] ?? null,
            'order' => $filtres["categorie_filtres[ordreAlpha]"] ?? null,
            'asc' => $filtres["categorie_filtres[asc]"] ?? null,
            'recherche' => $filtres["categorie_filtres[recherche]"] ?? null,
            /* 'os' => $filtres['os'] ?? null,
             'langages' => $filtres['langages'] ?? null,
              'editor' => $filtres['editor'] ?? null */
        ];
        $logger->info('Debug info:', ['data' => $filtres]);

        $produits = $produitRepo->findByCategoryAndFilter($categorie, $filtres);        // fonction de produit repository qui filtre les produits
        return $this->json([
            'produits' => $produits,
            'categorie' => $categorie->getNom(),
            'directory' => 'uploadedFiles/produitImages/' . $slugger->slug($categorie->getNom())
        ]);
    }
}