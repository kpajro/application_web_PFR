<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Repository\PanierRepository;
use App\Service\PanierHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PanierController extends AbstractController
{
    public function __construct(private PanierHandler $panierHandler)
    {
    }


    #[Route('/panier/view', name: 'app_panier_view')]
    public function viewPanier(Request $request) : Response
    {
        $panier = $this->panierHandler->getActivePanier($this->getUser(), $request);
        $produits = $panier->getProduits();
        $prixTotal = 0;

        foreach ($produits as $produit) {
            $prix = $produit->getPrix();
            $prixTotal += $prix;
        }

        return $this->render('/panier/view.html.twig', [
            'panier' => $panier,
            'produits' => $produits,
            'prixTotal' => $prixTotal
        ]);
    }

    #[Route('/panier/create/', name: 'app_panier_create')]
    public function createPanier(EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();
        $panier = $this->panierHandler->createNewPanier($user);

        $em->persist($panier);
        $em->flush();

        return new Response('', 200);
    }
}