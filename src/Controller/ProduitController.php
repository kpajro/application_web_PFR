<?php

namespace App\Controller;

use App\Entity\Produit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProduitController extends AbstractController
{
    #[Route('/produit/{id}/page-produit', name: 'app_produit_view')]
    public function viewProduit(Produit $produit, ) : Response
    {
        return $this->render('produit/view.html.twig', [
            'produit' => $produit
        ]);
    }
}