<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\PanierProduits;
use App\Entity\Produit;
use App\Entity\Users;
use App\Repository\PanierRepository;
use App\Service\PanierHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PanierController extends AbstractController
{
    public function __construct(private PanierHandler $panierHandler)
    {
    }


    #[Route('/panier/view', name: 'app_panier_view')]
    public function viewPanier(Request $request, EntityManagerInterface $em) : Response
    {
        $panier = $this->panierHandler->getActivePanier($this->getUser(), $request);
        $prixTotal = $this->panierHandler->getPanierTotalPrice($panier);

        $amountChangeForm = $this->createFormBuilder(null, [
            'action' => $this->generateUrl('app_panier_view')
        ]);

        foreach ($panier->getPanierProduits() as $pp) {
            $produit = $pp->getProduit();
            $amount = $pp->getAmount();

            $amountChangeForm->add(strval($pp->getId()), NumberType::class, [
                'label' => 'Quantité',
                'mapped' => false,
                'data' => $amount,
            ]);
        }

        $form = $amountChangeForm->getForm();
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            foreach ($panier->getPanierProduits() as $pp) {
                $amountData = $form->get(strval($pp->getId()))->getData();
                $pp->setAmount($amountData);

                $em->persist($pp);
            }
            $em->flush();
            return $this->redirectToRoute('app_panier_view');
        }

        return $this->render('/panier/view.html.twig', [
            'panier' => $panier,
            'prixTotal' => $prixTotal,
            'form' => $form,
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