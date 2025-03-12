<?php

namespace App\Controller;

use App\Entity\Panier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PanierController extends AbstractController
{
    #[Route('/panier/{id}/view', name: 'app_view_panier')]
    public function viewPanier(Panier $panier) : Response
    {
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
}