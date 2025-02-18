<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\BOCategorieFormType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/categories')]
#[IsGranted('ROLE_ADMIN')]
class BOCategoriesController extends AbstractController
{
    #[Route('', name: 'app_admin_categories_list')]
    public function categories(CategorieRepository $catRepo) : Response
    {
        $categories = $catRepo->findAll();

        return $this->render('/admin/categories/list.html.twig', [
            'categories' => $categories
        ]);
    }

    #[Route('/create', name: 'app_admin_categories_create')]
    public function categoriesCreate(EntityManagerInterface $em, Request $request) : Response
    {
        $categorie = new Categorie;
        $form = $this->createForm(
            BOCategorieFormType::class, 
            $categorie,
            ['action' => $this->generateUrl('app_admin_categories_create')] 
        );
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $categorie->setNbProduits(0);
            $em->persist($categorie);
            $em->flush();

            return $this->redirectToRoute('app_admin_categories_list');
        }

        return $this->render('elements/form_backoffice.html.twig', [
            'title' => 'Créer une nouvelle catégorie',
            'btnAction' => 'Créer',
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_categories_edit')]
    public function categoriesEdit(EntityManagerInterface $em, Request $request, Categorie $categorie) : Response
    {
        $form = $this->createForm(
            BOCategorieFormType::class, 
            $categorie,
            ['action' => $this->generateUrl('app_admin_categories_edit', ['id' => $categorie->getId()])] 
        );
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($categorie);
            $em->flush();

            return $this->redirectToRoute('app_admin_categories_list');
        }

        return $this->render('elements/form_backoffice.html.twig', [
            'title' => 'Modification de la catégorie ' . $categorie->getNom(),
            'btnAction' => 'Modifier',
            'deletable' => true,
            'deleteAction' => 'Supprimer la catégorie',
            'deleteWarning' => 'Êtes-vous sûr(e) de vouloir supprimer "' . $categorie->getNom() . '" ? Cette action est irréversible et etraînera la suppression de tous les produits dans cette catégorie.',
            'deleteLink' => $this->generateUrl('app_admin_categories_delete', ['id' => $categorie->getId()]),
            'form' => $form
        ]);
    }

    #[Route('/{id}/delete', name:'app_admin_categories_delete')]
    public function categoriesDelete(Categorie $categorie, EntityManagerInterface $em) : Response
    {
        $em->remove($categorie);
        $em->flush();

        return $this->redirectToRoute('app_admin_categories_list');
    }
}