<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Repository\PanierProduitsRepository;
use App\Repository\PanierRepository;
use App\Service\PanierHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/admin/paniers')]
#[IsGranted('ROLE_ADMIN')]
class BOPaniersController extends AbstractController
{
    public function __construct(private PanierHandler $panierHandler)
    {
        
    }

    #[Route('', name: 'app_admin_paniers_list')]
    public function listPaniers (PanierRepository $panierRepository) : Response
    {
        $paniers = $panierRepository->findAll();

        return $this->render('/admin/paniers/list.html.twig', [
            'paniers' => $paniers
        ]);
    }

    #[Route('/{id}/view', name: 'app_admin_paniers_view')]
    public function viewPanier (Panier $panier, Request $request, EntityManagerInterface $em) : Response
    {
        $produits = $panier->getProduits();
        $prixTotal = $this->panierHandler->getPanierTotalPrice($panier);

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

    #[Route('/check-all', name: 'app_admin_paniers_check_all')]
    public function checkAllPaniers(PanierRepository $panierRepo, EntityManagerInterface $em, Request $request) : Response
    {
        $paniers = $panierRepo->findAll();

        
        foreach ($paniers as $panier) {
            $this->panierHandler->checkPanierLifespan($panier);
        }
        
        $em->flush();
        
        $origin = $request->headers->get('referer');
        if ($origin !== null) {
            $this->addFlash('notice', 'L\'état des paniers a été mis à jour.');
            return $this->redirectToRoute('app_admin_paniers_list');
        }

        return new Response('', 200);
    }

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