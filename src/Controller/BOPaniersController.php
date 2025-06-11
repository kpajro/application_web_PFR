<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Repository\PanierRepository;
use App\Service\PanierHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


/**
 * Controller qui gère toutes les routes de la gestion des paniers dans le Back Office
 * - Connexion en tant qu'administrateur requise
 * - Toutes les routes ont pour préfixe '/admin/paniers'
 */
#[Route('/admin/paniers')]
#[IsGranted('ROLE_ADMIN')]
class BOPaniersController extends AbstractController
{
    public function __construct(private PanierHandler $panierHandler)
    {
        
    }

    /**
     * Route qui liste tous les paniers existant dans le site : '' ('app_admin_paniers_list')
     * @return Response Template twig '/admin/paniers/list.html.twig'
     */
    #[Route('', name: 'app_admin_paniers_list')]
    public function listPaniers (PanierRepository $panierRepository) : Response
    {
        $paniers = $panierRepository->findAll();

        return $this->render('/admin/paniers/list.html.twig', [
            'paniers' => $paniers
        ]);
    }

    /**
     * Route pour focus sur un panier en particulier et interagir avec (les modifications sont limitées pour des raisons de privacy) : '/{id}/view' ('app_admin_paniers_view')
     * - Destiné à une modale
     * @param Panier $panier Panier ciblé par le chemin de la route
     * @return Response Template Twig 'admin/paniers/view.html.twig'
     */
    #[Route('/{id}/view', name: 'app_admin_paniers_view')]
    public function viewPanier (Panier $panier, Request $request, EntityManagerInterface $em) : Response
    {
        $produits = $panier->getProduits();
        $prixTotal = $this->panierHandler->getPanierTotalPrice($panier);

        // formulaire créé pour modifier l'état du panier (c'est la seule modification possible)
        $form = $this->createFormBuilder(
            $panier, 
            ['action' => $this->generateUrl('app_admin_paniers_view', ['id' => $panier->getId()])]
        )
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    'Actif' => 1,
                    'Inactif' => 2,
                    'Supprimable' => 3
                ]
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $etat = $form->get('etat')->getData();
            $panier->setEtat($etat);

            $em->persist($panier);
            $em->flush();

            return $this->redirectToRoute('app_admin_paniers_list');
        }

        return $this->render('admin/paniers/view.html.twig', [
            'panier' => $panier,
            'prixTotal' => $prixTotal,
            'produits' => $produits,
            'form' => $form
        ]);
    }

    /**
     * Route pour vérifier l'état de tous les paniers existants et automatiquement le modifier si nécessaire : /check-all ('app_admin_paniers_check_all)
     * - Destinée à être accedée automatiquement par un CORS pour que l'état de tous les paniers soit à jour automatiquement
     * @return Response Réponse HTTP_OK
     */
    #[Route('/check-all', name: 'app_admin_paniers_check_all')]
    public function checkAllPaniers(PanierRepository $panierRepo, EntityManagerInterface $em, Request $request) : Response
    {
        $paniers = $panierRepo->findAll();

        // la durée de vie de chaque panier est vérifiée pour determiner si un changement doit être fait
        foreach ($paniers as $panier) {
            $this->panierHandler->checkPanierLifespan($panier);     // fonction du panierhandler (service) pour vérifier la durée de vie d'un panier
        }
        
        $em->flush();
        
        // dans le cas où la requête à été faite manuellement (via le bouton disponible sur la liste des paniers) et non pas par un script
        $origin = $request->headers->get('referer');
        if ($origin !== null) {
            $this->addFlash('notice', 'L\'état des paniers a été mis à jour.');
            return $this->redirectToRoute('app_admin_paniers_list'); // redirection vers la liste des paniers
        }

        return new Response('', 200);
    }

    /**
     * Route pour supprimer automatiquement tous les paniers qui sont à l'état supprimable : '/remove-all-deletable' ('app_admin_paniers_remove_deletable')
     * @return Response Redirection vers la liste des paniers
     */
    #[Route('/remove-all-deletable', name: 'app_admin_paniers_remove_deletables')]
    public function removePaniers(PanierRepository $panierRepo, EntityManagerInterface $em) : Response
    {
        $paniersToDelete = $panierRepo->findBy(['etat' => 3]);

        foreach ($paniersToDelete as $panier) {
            $em->remove($panier);
        }

        $em->flush();

        return $this->redirectToRoute('app_admin_paniers_list');
    }
}