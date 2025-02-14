<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Users;
use App\Form\BOCategorieFormType;
use App\Form\BOUserEditFormType;
use App\Repository\CategorieRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('', name:'app_admin_home')]
    public function index() : Response 
    {
        return $this->render('admin/index.html.twig', [

        ]);
    }

    #[Route('/users', name:'app_admin_users_list')]
    public function users(UsersRepository $usersRepo) : Response
    {
        $users = $usersRepo->findAll();

        return $this->render('/admin/users/list.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/users/{id}/edit', name: 'app_admin_users_edit')]
    public function userEdit(Users $user, Request $request, EntityManagerInterface $em) : Response
    {
        $form = $this->createForm(
            BOUserEditFormType::class, 
            $user,
            ['action' => $this->generateUrl('app_admin_users_edit', ['id' => $user->getId()])]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_admin_users_list');
        }

        return $this->render('elements/form_backoffice.html.twig', [
            'form' => $form,
            'title' => "Modification d'information utilisateur (" . $user->getEmail() . ").",
            'deleteLink' => $this->generateUrl('app_admin_users_delete', ['id' => $user->getId()]),
            'btnAction' => "Enregistrer"
        ]);
    }

    #[Route('/users/{id}/delete', name:'app_admin_users_delete')]
    public function userDelete(Users $user, EntityManagerInterface $em) : Response
    {
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('app_admin_users_list');
    }

    #[Route('/categories', name: 'app_admin_categories_list')]
    public function categories(CategorieRepository $catRepo) : Response
    {
        $categories = $catRepo->findAll();

        return $this->render('/admin/categories/list.html.twig', [
            'categories' => $categories
        ]);
    }

    #[Route('/categories/create', name: 'app_admin_categories_create')]
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

    #[Route('/categories/{id}/edit', name: 'app_admin_categories_edit')]
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
            'title' => 'Créer une nouvelle catégorie',
            'btnAction' => 'Créer',
            'deleteLink' => $this->generateUrl('app_admin_categories_delete', ['id' => $categorie->getId()]),
            'form' => $form
        ]);
    }

    #[Route('/categories/{id}/delete', name:'app_admin_categories_delete')]
    public function categoriesDelete(Categorie $categorie, EntityManagerInterface $em) : Response
    {
        $em->remove($categorie);
        $em->flush();

        return $this->redirectToRoute('app_admin_categories_list');
    }
}