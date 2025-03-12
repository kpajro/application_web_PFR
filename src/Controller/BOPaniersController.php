<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/admin/paniers')]
#[IsGranted('ROLE_ADMIN')]
class BOPaniersController extends AbstractController
{
    #[Route('', name: 'app_admin_paniers_list')]
    public function listPaniers (PanierRepository $panierRepository) : Response
    {
        $paniers = $panierRepository->findAll();

        return $this->render('/admin/paniers/list.html.twig', [
            'paniers' => $paniers
        ]);
    }

    #[Route('/{id}/view', name: 'app_admin_paniers_view')]
    public function viewPanier (Panier $panier) : Response
    {
        $produits = $panier->getProduits();
        $prixTotal = 0;
        foreach ($produits as $produit) {
            $prix = $produit->getPrix();
            $prixTotal += $prix;
        }

        return $this->render('admin/paniers/view.html.twig', [
            'panier' => $panier,
            'prixTotal' => $prixTotal
        ]);
    }
}