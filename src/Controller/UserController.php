<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;
use Dompdf\Dompdf;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Mailer\MailerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    private LoggerInterface $logger;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(LoggerInterface $logger, UserPasswordHasherInterface $passwordHasher)
    {
        $this->logger = $logger;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/profile/{id}', name: 'app_user_profile')]
    public function profile(Users $user): Response
    {
        $loggedUser = $this->getUser();
        if (!$loggedUser || $loggedUser !== $user) {
            throw new AccessDeniedException('Connexion au compte ciblé requise.');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'form' => $this->createForm(UserProfileFormType::class, $user)->createView()
        ]);
    }
    
    // Route pour le traitement du formulaire (AJAX ou POST depuis la modale)
    #[Route('/profile/{id}/parametre', name: 'app_profile_settings')]
    public function updateProfile(Users $user, Request $request, EntityManagerInterface $em): Response
    {
        $loggedUser = $this->getUser();
        if (!$loggedUser || $user !== $loggedUser) {
            throw new AccessDeniedException('Connexion au compte ciblé requise');
        }

        $form = $this->createForm(UserProfileFormType::class, $user, [
            'action' => $this->generateUrl('app_profile_settings', ['id' => $user->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement mot de passe
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('plainPassword')->getData();

            if ($newPassword) {
                if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $form->get('currentPassword')->addError(new FormError('Mot de passe actuel incorrect.'));
                    return $this->render('/user/profileModale.html.twig', [
                        'user' => $user,
                        'form' => $form
                    ]);
                } else {
                    $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
                }
            }

            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour.');

            return $this->redirectToRoute('app_user_profile', ['id' => $user->getId()]);
        }

        return $this->render('/user/profileModale.html.twig', [
            'user' => $user,
            'form' => $form
        ]);
    }

    #[Route('/profile/{id}/supprimer-mon-profil', name: 'app_profile_delete_account')]
    public function deleteAccount(Users $user, EntityManagerInterface $em, Request $request, TokenStorageInterface $tokenStorage): Response
    {
        $loggedUser = $this->getUser();

        if (!$loggedUser || $user !== $loggedUser) {
            throw new AccessDeniedException('Connexion au compte ciblé requise');
        }

        $formBuilder = $this->createFormBuilder(null, [
            'action' => $this->generateUrl('app_profile_delete_account', ['id' => $user->getId()])
        ]);
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $panierActif = $user->getPanierActif();
            if ($panierActif !== null) {
                $panierActif->setUser(null);
                $em->persist($panierActif);
            }
            $paniers = $user->getPaniers();
            foreach ($paniers as $panier) {
                if ($panier) {
                    $panier->setUser(null);
                    $em->persist($panier);
                }
            }

            $user->setPanierActif(null);
            $em->persist($user);

            //dd($user);
            $em->remove($user);
            $em->flush();

            $request->getSession()->invalidate();
            $tokenStorage->setToken(null); // TokenStorageInterface
            return $this->redirectToRoute('app_index');
        }

        return $this->render('user/delete-account.html.twig', [
            'user' => $user,
            'form'=> $form
        ]);
    }

    #[Route('/profile/{id}/generer-données', name: 'app_profile_generate_data')]
    public function generateData(Users $user, SluggerInterface $slugger): Response
    {
        $loggedUser = $this->getUser();
        if (!$loggedUser || $loggedUser !== $user) {
            throw new AccessDeniedException('Connexion au compte ciblé requise.');
        }

        $html = $this->renderView('/user/user-data.html.twig', [
            'user' => $user
        ]);
        $filename = $slugger->slug($user->getName()) . '_' . $slugger->slug($user->getFirstname()) . '-data.pdf';
        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->render();

        return new Response(
            $pdf->stream($filename, []),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );
    }

    #[Route('/profile/{id}/change-password', name: 'change_password', methods: ['GET'])]
    public function changePassword(Request $request, int $id): Response
    {
        $user = $this->getUser();
        if (!$user || $user->getId() !== $id) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Mot de passe changé avec succès.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/change_password_modal.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
