<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\BOUserEditFormType;
use App\Repository\UsersRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class BOUsersController extends AbstractController
{
    #[Route('', name:'app_admin_users_list')]
    public function users(UsersRepository $usersRepo) : Response
    {
        $users = $usersRepo->findAll();

        return $this->render('/admin/users/list.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_users_edit')]
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
            'deletable' => true,
            'deleteAction' => 'Supprimer l\'utilisateur',
            'deleteWarning' => 'Êtes-vous sûr(e) de vouloir supprimer "' . $user->getEmail() . '" ? Cette action est irréversible.',
            'deleteLink' => $this->generateUrl('app_admin_users_delete', ['id' => $user->getId()]),
            'btnAction' => "Modifier"
        ]);
    }

    #[Route('/{id}/delete', name:'app_admin_users_delete')]
    public function userDelete(Users $user, EntityManagerInterface $em) : Response
    {
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('app_admin_users_list');
    }

    #[Route('/check-user-inactivity', name:'app_admin_users_inactivity_check')]
    public function checkInactivity(UsersRepository $userRepo, EntityManagerInterface $em): Response
    {
        $users = $userRepo->findAll();

        foreach ($users as $user) {
            $lastLogin = $user->getLastLogIn();
            $currentDate = new \DateTimeImmutable();

            $diff = date_diff($lastLogin, $currentDate);
            if ($diff->y >= 2) {
                $em->remove($user);
            }
        }
        $em->flush();

        return $this->redirectToRoute('app_admin_users_list');
    }
}