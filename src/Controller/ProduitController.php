<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Produit;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/produit/{id}/donner-avis', name: 'app_produit_donner_avis')]
    public function donnerAvis(Produit $produit, Request $request, EntityManagerInterface $em): Response
    {
        $avis = new Avis();
        $form = $this->createForm(AvisFormType::class, $avis, [
            'action' => $this->generateUrl('app_produit_donner_avis', ['id' => $produit->getId()])
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            
        }

        return $this->render('/produit/donner-avis.html.twig', [
            'produit' => $produit,
            'form' => $form
        ]);
    }
}