<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\BOProduitFormType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/produits')]
#[IsGranted('ROLE_ADMIN')]
class BOProduitsController extends AbstractController
{
    #[Route('', name: 'app_admin_produits_list')]
    public function produits(ProduitRepository $produitRepo) : Response
    {
        $produits = $produitRepo->findAll();

        return $this->render('admin/produits/list.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/create', name: 'app_admin_produits_create')]
    public function produitCreate(Request $request, EntityManagerInterface $em) : Response
    {
        $produit = new Produit;
        $form = $this->createForm(BOProduitFormType::class, $produit, [
            'action' => $this->generateUrl('app_admin_produits_create')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $prCategorie = $produit->getCategorie();
            $prCategorie->setNbProduits($prCategorie->getNbProduits() + 1);

            $em->persist($produit);
            $em->persist($prCategorie);
            $em->flush();

            return $this->redirectToRoute('app_admin_produits_list');
        }

        return $this->render('elements/form_backoffice.html.twig', [
            'form' => $form,
            'title' => 'Création de produit',
            'btnAction' => 'Créer'
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_produits_edit')]
    public function produitEdit(Produit $produit, Request $request, EntityManagerInterface $em) : Response
    {
        $form = $this->createForm(BOProduitFormType::class, $produit, [
            'action' => $this->generateUrl('app_admin_produits_edit', ['id' => $produit->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($produit);
            $em->flush();

            return $this->redirectToRoute('app_admin_produits_list');
        }

        return $this->render('elements/form_backoffice.html.twig', [
            'form' => $form,
            'title' => 'Modification du produit "' . $produit->getNom() . '".',
            'btnAction' => 'Enregistrer',
            'deleteLink' => $this->generateUrl('app_admin_produits_delete', ['id' => $produit->getId()])
        ]);
    }

    #[Route('/{id}/delete', name:'app_admin_produits_delete')]
    public function produitDelete(Produit $produit, EntityManagerInterface $em) : Response
    {
        $prCategorie = $produit->getCategorie();
        $prCategorie->setNbProduits($prCategorie->getNbProduits() - 1);

        $em->persist($prCategorie);
        $em->remove($produit);
        $em->flush();

        return $this->redirectToRoute('app_admin_produits_list');
    }
}