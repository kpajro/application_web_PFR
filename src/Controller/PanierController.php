<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\Users;
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
        $prixTotal = $this->panierHandler->getPanierTotalPrice($panier);

        return $this->render('/panier/view.html.twig', [
            'panier' => $panier,
            'prixTotal' => $prixTotal
        ]);
    }

    #[Route('/panier/add-produit/{produit}', name: 'app_panier_add_product')]
    public function addProductToPanier(Produit $produit, Request $request, EntityManagerInterface $em) : Response 
    {
        $amount = $produit->isBulkSale() ? $produit->getBulkSize() : 1;
        $panier = $this->panierHandler->getActivePanier($this->getUser(), $request);
        $panier->addProduit($produit, $em, $amount);

        $em->persist($panier);
        $em->flush();
        
        return new Response('Produit ajouté au panier', 200);
    }

    #[Route('/panier/remove-produit/{produit}', name: 'app_panier_remove_product')]
    public function removeProductFromPanier(Produit $produit, Request $request, EntityManagerInterface $em) : Response
    {
        $panier = $this->panierHandler->getActivePanier($this->getUser(), $request);
        $panier->removeProduit($produit, $em);

        $em->persist($panier);
        $em->flush();

        return new Response('Produit retiré du panier', 200);
    }

    #[Route('/panier/create/', name: 'app_panier_create')]
    public function createPanier(EntityManagerInterface $em, Request $request) : Response
    {
        /** @var Users $user */
        $user = $this->getUser();
        $session = $request->getSession();
        $panier = new Panier($user);

        if (!$user) {
            $session->set('panier', $panier->getId());    
        } else {
            $user->addPanier($panier);

            if ($user->getPanierActif() !== null) {
                $oldPanier = $user->getPanierActif();
                $oldPanier->setEtat(2);
                $user->setPanierActif($panier);
            }

            $em->persist($user);
        }

        $em->persist($panier);
        $em->flush();

        return new Response('', 200);
    }
}