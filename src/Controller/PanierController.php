<?php

namespace App\Controller;

use App\Entity\Panier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PanierController extends AbstractController
{
    #[Route('/panier/{id}/view', name: 'app_panier_view')]
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

    #[Route('/panier/create/', name: 'app_panier_create')]
    public function createPanier(EntityManagerInterface $em) : Response
    {
        $panier = new Panier();
        $panier->setUser($this->getUser() ? $this->getUser() : null);
        $panier->setEtat(1);
        $panier->setCreatedAt(new \DateTimeImmutable('now'));

        $em->persist($panier);
        $em->flush();

        return new Response('', 200);
    }
}