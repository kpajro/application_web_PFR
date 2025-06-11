<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\Users;
use App\Service\PanierHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller dédié à toutes les routes des fonctionnalités du panier
 */
class PanierController extends AbstractController
{
    public function __construct(private PanierHandler $panierHandler)
    {
    }

    /**
     * Route pour visualiser le panier, dédié à une modale : '/panier/view' ('app_panier_view')
     * @return Response Template twig '/panier/view.html.twig'
     */
    #[Route('/panier/view', name: 'app_panier_view')]
    public function viewPanier(Request $request, EntityManagerInterface $em) : Response
    {
        $panier = $this->panierHandler->getActivePanier($this->getUser(), $request);
        $prixTotal = $this->panierHandler->getPanierTotalPrice($panier);
        $amountChangeForm = $this->createFormBuilder(null, [        // formulaire pour modifier la quantité d'un produit dans le panier
            'action' => $this->generateUrl('app_panier_view'),
            'attr' => ['id' => 'panier-form']
        ]);

        foreach ($panier->getPanierProduits() as $pp) {
            // on ajoute un champ pour chaque produit qui prend commme nom l'id du produit
            // ce champ aura pour valeur la quantité du produit désiré dans le panier
            // il prend comme valeur de base la quantité du produit déjà existante
            $produit = $pp->getProduit();
            $amount = $pp->getAmount();
            $amountChangeForm->add(strval($pp->getId()), NumberType::class, [
                'label' => 'Quantité',
                'mapped' => false,
                'data' => $amount,
                'attr' => [
                    'disabled' => true,
                    'class' => 'panier-input',
                    'data-id' => $pp->getId()
                ]
            ]);
        }

        $form = $amountChangeForm->getForm();
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // quand le formulaire est submit on change la valeur de la quantité du produit dans le panier, pour chaque produit
            // si le valeur est différente, elle sera mise à jour, sinon elle restera la même
            foreach ($panier->getPanierProduits() as $pp) {
                $amountData = $form->get(strval($pp->getId()))->getData();
                $pp->setAmount($amountData);

                $em->persist($pp);
            }
            $em->flush();
            return new Response('Panier mis à jour', 200);      // une fois l'opération on envoie une reponse HTTP_OK qui sera récupérée par le frontend avec le js (le formulaire est géré par le controller panier_controller)
        }

        return $this->render('/panier/view.html.twig', [
            'panier' => $panier,
            'prixTotal' => $prixTotal,
            'form' => $form,
        ]);
    }

    /**
     * Route pour ajouter un nouveau produit au panier : '/panier/add-produit/{id}' ('app_panier_add_product')
     * @param Produit $produit Produit ciblé par le chemin de la route
     * @return Response Une réponse HTTP_OK
     */
    #[Route('/panier/add-produit/{id}', name: 'app_panier_add_product')]
    public function addProductToPanier(Produit $produit, Request $request, EntityManagerInterface $em) : Response 
    {
        $amount = $produit->isBulkSale() ? $produit->getBulkSize() : 1;
        $panier = $this->panierHandler->getActivePanier($this->getUser(), $request);
        $panier->addProduit($produit, $em, $amount);

        $em->persist($panier);
        $em->flush();
        
        return new Response('Produit ajouté au panier', 200);
    }

    /**
     * Route pour supprimer un produit du panier : '/panier/remove-produit/{produit}' ('app_panier_remove_product')
     * @param Produit $produit Produit ciblé par le chemin de la route
     * @return Response Reponse HTTP_OK
     */
    #[Route('/panier/remove-produit/{produit}', name: 'app_panier_remove_product')]
    public function removeProductFromPanier(Produit $produit, Request $request, EntityManagerInterface $em) : Response
    {
        $panier = $this->panierHandler->getActivePanier($this->getUser(), $request);
        $panier->removeProduit($produit, $em);

        $em->persist($panier);
        $em->flush();

        return new Response('Produit retiré du panier : ' . $produit->getNom(), 200);
    }

    /**
     * Route pour créer un nouveau panier (pas utilisée) : '/panier/create' ('app_panier_create')
     * @return Response Réponse HTTP_OK
     */
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