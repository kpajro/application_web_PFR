<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserProfileFormType;
use Doctrine\ORM\EntityManagerInterface;


class UserController extends AbstractController
{
    #[Route('/profile', name: 'app_user_profile')]
    public function profile(): Response
    {
        return $this->render('user/profile.html.twig');
    }

    #[Route('/profile/parametres', name: 'app_user_settings')]
    public function account(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush(); // enregistre les modifs
            $this->addFlash('success', 'Profile updated successfully!');
        }

        return $this->redirectToRoute('app_user_profile');
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
}
