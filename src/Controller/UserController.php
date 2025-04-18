<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;
use App\Repository\UsersRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


use Symfony\Component\Mailer\MailerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;



class UserController extends AbstractController
{
    #[Route('/profile/{id}', name: 'app_user_profile')]
    public function profile(int $id, UsersRepository $usersRepo): Response
    {
        $user = $this->getUser();
    
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté pour voir ce profil.');
        }
    
        $targetUser = $usersRepo->find($id);
    
        if (!$targetUser) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }
    
        if ($user !== $targetUser) {
            throw new AccessDeniedException('Vous ne pouvez accéder qu’à votre propre profil.');
        }
    
        return $this->render('user/profile.html.twig', [
            'user' => $targetUser,
            'form' => $this->createForm(UserProfileFormType::class, $targetUser)->createView()
        ]);
    }
    
    // Route pour le traitement du formulaire (AJAX ou POST depuis la modale)
    #[Route('/profile/{id}/parametre', name: 'app_user_profile_update')]
    public function updateProfile(int $id, Request $request, UsersRepository $usersRepo, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
    
        if (!$user) {
            throw new AccessDeniedException();
        }
    
        $targetUser = $usersRepo->find($id);
    
        if (!$targetUser || $user !== $targetUser) {
            throw new AccessDeniedException();
        }
    
        $form = $this->createForm(UserProfileFormType::class, $targetUser);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour.');
        }
    
        return $this->redirectToRoute('app_user_profile', ['id' => $id]);
    }


    // #[Route('/settings', name: 'settings')]
    // public function settings(): Response
    // {
    //     return $this->render('settings.html.twig');
    // }

    // #[Route('/commandes', name: 'commandes')]
    // public function commandes(): Response
    // {
    //     return $this->render('commandes.html.twig');
    // }

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/test-mail', name: 'test_mail')]
    public function testMail(MailerInterface $mailer): Response
    {
        $email = (new TemplatedEmail())
            ->from('no_reply@projetfilrouge.com')
            ->to('ton-email@exemple.com')
            ->subject('Test Email')
            ->html('<p>Ce message est un test de l\'envoi d\'email.</p>');
        
        try {
            $mailer->send($email);
            $this->logger->info('Email envoyé à : ton-email@exemple.com'); // Utilisation du logger injecté
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi de l\'email: '.$e->getMessage()); // Utilisation du logger injecté
        }

        return new Response('Email envoyé!');
    }
}
