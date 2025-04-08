<?php

namespace App\Controller;

use App\Form\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserProfileFormType;
use Doctrine\Persistence\ManagerRegistry;



class UserController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function profile(): Response
    {
        return $this->render('user/profile.html.twig');
    }

    #[Route('/account', name: 'account')]
public function account(Request $request, ManagerRegistry $doctrine): Response
{
    $user = $this->getUser();
    $form = $this->createForm(UserProfileFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em = $doctrine->getManager();
        $em->flush(); // enregistre les modifs
        $this->addFlash('success', 'Profile updated successfully!');
    }

    return $this->render('user/account.html.twig', [
        'profileForm' => $form->createView(),
    ]);
}


    #[Route('/settings', name: 'settings')]
    public function settings(): Response
    {
        return $this->render('settings.html.twig');
    }

    #[Route('/commandes', name: 'commandes')]
    public function commandes(): Response
    {
        return $this->render('commandes.html.twig');
    }
}
