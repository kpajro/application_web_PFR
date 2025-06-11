<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Service\PanierHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Controller qui gère les routes de connexion et de création de compte
 */
class ConnectionController extends AbstractController
{
    /**
     * Route pour l'enregistrement d'un compte utilisateur : '/enregistrement' ('app_register')
     * @return Response Template twig 'connection/register.html.twig'
     */
    #[Route('/enregistrement', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword)); //encodage du mdp
            $user->setCreatedAt(new \DateTimeImmutable());

            // on créé un nouveau panier dont le propriétaire est l'utilisateur et on le met en panier actif
            $panier = new Panier($user);
            $user->addPanier($panier);
            $user->setPanierActif($panier);

            $entityManager->persist($panier);
            $entityManager->persist($user);
            $entityManager->flush(); //utilisateur sauvegardé dans la bdd

            return $this->redirectToRoute('app_index');     // redirection vers la page d'accueil du site
        }

        return $this->render('connection/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    /**
     * Route pour la connexion sécurisée au site : '/connexion' ('app_login')
     * @return Response Template twig 'connection/login.html.twig
     */
    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('connection/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * Route qui gère la deconnexion du site (gérée automatiquement par Symfony)
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}