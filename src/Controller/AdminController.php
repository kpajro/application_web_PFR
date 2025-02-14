<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\BOUserEditFormType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name:'app_admin_home')]
    #[IsGranted('ROLE_ADMIN')]
    public function index() : Response 
    {
        return $this->render('admin/index.html.twig', [

        ]);
    }

    #[Route('/users', name:'app_admin_users_list')]
    #[IsGranted('ROLE_ADMIN')]
    public function users(UsersRepository $usersRepo) : Response
    {
        $users = $usersRepo->findAll();

        return $this->render('/admin/users/list.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/users/{id}/edit', name: 'app_admin_users_edit')]
    #[IsGranted('ROLE_ADMIN')]
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

        return $this->render('elements/editform_backoffice.html.twig', [
            'form' => $form,
            'editTitle' => "Modification d'information utilisateur (" . $user->getEmail() . ").",
            'btnAction' => "Enregistrer"
        ]);
    }

    #[Route('/users/{id}/remove', name:'app_admin_users_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function userDelete(Users $user, EntityManagerInterface $em) : Response
    {
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('app_admin_users_list');
    }
}