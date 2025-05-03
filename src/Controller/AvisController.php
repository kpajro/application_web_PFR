<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\AvisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AvisController extends AbstractController
{
    #[Route('/produit/{id}/get-avis/', name: 'app_avis_get_produit_avis')]
    public function getAvis(Produit $produit, AvisRepository $avisRepo): Response
    {
        $avis = $avisRepo->findBy(['produit' => $produit]);
        return $this->render('/avis/produit-avis.html.twig', [
            'avis' => $avis,
            'produit' => $produit
        ]);
    }
}