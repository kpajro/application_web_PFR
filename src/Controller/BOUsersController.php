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

/**
 * Controller qui gère la gestion des utilisateurs dans le Back Office
 * - Requiert une connextion en tant qu'administrateur
 * - Toutes les routes ont comme préfixe '/admin/users'
 */
#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class BOUsersController extends AbstractController
{
    /**
     * Route qui liste tous les utilisateurs du site : '' ('app_admin_users_list)
     * @return Response Template twig '/admin/users/list.html.twig'
     */
    #[Route('', name:'app_admin_users_list')]
    public function users(UsersRepository $usersRepo) : Response
    {
        $users = $usersRepo->findAll();

        return $this->render('/admin/users/list.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * Route qui permet de modifier un utilisateur : '/{id}/edit'
     * @param Users $user Utilisateur ciblé par le chemin de la route
     * @return Response Template Twig 'elements/form_backoffice.html.twig' (template commun modifiable via paramètres à envoyer)
     */
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
            'deletable' => true,    // bouton de suppression actif
            'deleteAction' => 'Supprimer l\'utilisateur',
            'deleteWarning' => 'Êtes-vous sûr(e) de vouloir supprimer "' . $user->getEmail() . '" ? Cette action est irréversible.',    // message de confirmation de suppression
            'deleteLink' => $this->generateUrl('app_admin_users_delete', ['id' => $user->getId()]),     // lien vers la suppression de l'utilisateur
            'btnAction' => "Modifier"
        ]);
    }

    /**
     * Route pour supprimer un administrateur : '/{id}/delete' ('app_admin_users_delete')
     * @param Users $user Utilisateur ciblé par le chemin de la route
     * @return Response Redirection vers la liste des utilisateurs
     */
    #[Route('/{id}/delete', name:'app_admin_users_delete')]
    public function userDelete(Users $user, EntityManagerInterface $em) : Response
    {
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('app_admin_users_list');
    }

    /**
     * Route qui vérifie combien de temps depuis la dernière connexion pour tous les utilisateurs du site : '/check-user
     *  - Si un utilisateur ne s'est pas connecté depuis + de 2 ans, son compte est supprimé
     * @return Response Redirection vers la liste des utilisateurs
     */
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