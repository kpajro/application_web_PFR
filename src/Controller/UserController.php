<?php

namespace App\Controller;

use App\Entity\Panier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Form\UserProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;
use App\Form\ChangePasswordFormType;
use Dompdf\Dompdf;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormError;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Controller qui prend en charge toutes les fonctions de routes liées à la page du profil utilisateur
 */
class UserController extends AbstractController
{
    private LoggerInterface $logger;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(LoggerInterface $logger, UserPasswordHasherInterface $passwordHasher)
    {
        $this->logger = $logger;
        $this->passwordHasher = $passwordHasher;
    }
    public function __invoke(#[CurrentUser] ?Users $user): JsonResponse
    {
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $paiments = $user->getPaiements();

        return new JsonResponse([
            'user' => $user,
        ]);
    }

    /**
     * Route d'affichage du profil utilisateur : '/profile/{id}' ('app_user_profile')
     * @param Users $user Utilisateur dont l'id est renseigné dans le chemin, commun à toutes les routes de ce controller
     * @return Response Template twig 'user/profile.html.twig'
     */
    #[Route('/profile/{id}', name: 'app_user_profile')]
    public function profile(Users $user): Response
    {
        // on vérifie que l'utilisateur connecté correspond à l'utilisateur ciblé par le chemin
        $loggedUser = $this->getUser();
        if (!$loggedUser || $loggedUser !== $user) {
            throw new AccessDeniedException('Connexion au compte ciblé requise.');
        }
        $paiments = $user->getPaiements();

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'paiements' => $paiments
        ]);
    }
    
    /**
     * Route de modification d'information de profil, déstinée à être affichée dans une modale : '/profile/{id}/parametre' ('app_profile_settings')
     * @param Users $user Utilisateur ciblé par le chemin de la route
     * @return Response Template twig 'user/profileModale.html.twig'
     */
    #[Route('/profile/{id}/parametre', name: 'app_profile_settings')]
    public function updateProfile(Users $user, Request $request, EntityManagerInterface $em): Response
    {
        // si l'utilisateur connecté est différent de l'utilisateur ciblé, erreur envoyée
        $loggedUser = $this->getUser();
        if (!$loggedUser || $user !== $loggedUser) {
            throw new AccessDeniedException('Connexion au compte ciblé requise');
        }

        $form = $this->createForm(UserProfileFormType::class, $user, [
            'action' => $this->generateUrl('app_profile_settings', ['id' => $user->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour.');

            return $this->redirectToRoute('app_user_profile', ['id' => $user->getId()]);    // en cas de modification on rafraichit la page de profil
        }

        return $this->render('/user/profileModale.html.twig', [
            'user' => $user,
            'form' => $form
        ]);
    }

    /**
     * Route pour la suppression du profil utilisateur, destinée à être affichée dans une modale : '/profile/{id}/supprimer-mon-profil' ('app_profile_delete_account')
     * @param Users $user Utilisateur ciblé par le chemin de la route
     * @return Response Template twig 'user/delete-account.html.twig'
     */
    #[Route('/profile/{id}/supprimer-mon-profil', name:'app_profile_delete_account')]
    public function deleteAccount(Users $user, EntityManagerInterface $em, Request $request, TokenStorageInterface $tokenStorage, UserPasswordHasherInterface $hasher): Response
    {
        // vérification que l'utilisateur connecté correspond à l'utilisateur ciblé
        $loggedUser = $this->getUser();
        if (!$loggedUser || $user !== $loggedUser) {
            throw new AccessDeniedException('Connexion au compte ciblé requise');
        }

        $formBuilder = $this->createFormBuilder(null, [     // on créé un formulaire pour confirmer l'action
            'action' => $this->generateUrl('app_profile_delete_account', ['id' => $user->getId()])

        ])
            ->add('plainPassword', PasswordType::class, [       // on ajoute un champ mot de passe afin de confirmer l'action
                'label' => "Entrez votre mot de passe pour confirmer l'action.",
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe.',
                    ]),
                ],
                'label_attr' => ['font-semibold']
            ])
        ;
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $hasher->isPasswordValid($user, $form->get('plainPassword')->getData())) {      // avant de commencer la suppression, on vérifie que le mot de passe correspond bien
            // désactivation du panier actif de l'utilisateur, et on retire l'utilisateur du panier
            $panierActif = $user->getPanierActif();
            if ($panierActif !== null) {
                $panierActif->setUser(null);
                $em->persist($panierActif);
            }
            $paniers = $user->getPaniers();
            foreach ($paniers as $panier) {     // même chose pour les anciens paniers de l'utilisateur
                if ($panier) {
                    $panier->setUser(null);
                    $em->persist($panier);
                }
            }

            $user->setPanierActif(null);
            $em->persist($user);

            $em->remove($user);
            $em->flush();

            $request->getSession()->invalidate();       // on supprime l'utilisateur de la session
            $tokenStorage->setToken(null);      // TokenStorageInterface
            return $this->redirectToRoute('app_index');     // redirection vers l'accueil du site
        }

        return $this->render('user/delete-account.html.twig', [
            'user' => $user,
            'form'=> $form
        ]);
    }

    /**
     * Route pour le téléchargement des données utilisateur au format pdf : '/profile/{id}/generer-données' ('app_profile_generate_data')
     * @param Users $user Utilisateur ciblé par le chemin de la route
     * @return Response Fichier PDF de la template twig 'user/user-data.html.twig'
     */
    #[Route('/profile/{id}/generer-données', name: 'app_profile_generate_data')]
    public function generateData(Users $user, SluggerInterface $slugger): Response
    {
        // vérification que l'utilisateur ciblé correspond à l'utilisateur connecté
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

    /**
     * Route pour restorer un ancien panier utilisateur : '/profile/restore-panier/{id}' ('app_profile_restore_panier')
     * @param Panier $panier Panier ciblé par le chemin de la route
     * @return Response Redirection vers la route de profil
     */
    #[Route('/profile/restore-panier/{id}', name: 'app_profile_restore_panier')]
    public function restorePanier (Panier $panier, EntityManagerInterface $em) : Response 
    {
        // on vérifie que l'utilisateur connecté correspond à l'utilisateur propriétaire du panier
        /** @var Users $user */ 
        $user = $this->getUser();
        if ($user !== $panier->getUser()) {
            throw new AccessDeniedException('Le propriétaire du panier ne correspond pas à l\'utilisateur connecté.');
        }

        $currentPanier = $user->getPanierActif();
        $panierProduits = $panier->getPanierProduits();

        $currentPanier->setEtat(2);     // on passe le panier actuel à l'état inactif
        $newPanier = new Panier($user);     // création d'un nouveau panier qui va reprendre tous les paniers-produits du panier précédent 
        foreach ($panierProduits as $pp) {
            $produit = $pp->getProduit();
            $amount = $pp->getAmount();

            $newPanier->addProduit($produit, $em, $amount);
        }
        $user->addPanier($newPanier);
        $user->setPanierActif($newPanier);

        $em->persist($newPanier);
        $em->persist($panier);
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('app_user_profile', ['id' => $user->getId()]);
    }

    /**
     * Route pour modifier le mot de passe de l'utilisateur lorsqu'il est déjà connecté : '/profile/{id}/change-password' ('app_profile_change_password')
     * @param Users $user Utilisateur ciblé par le chemin de la route
     * @return Response Template twig 'user/change-password.html.twig'
     */
    #[Route('/profile/{id}/change-password', name: 'app_profile_change_password')]
    public function changePassword(Users $user, EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $hasher, TokenStorageInterface $tokenStorage) : Response
    {
        // vérification que l'utilisateur connecté correspond à l'utilisateur ciblé par la route
        $loggedUser = $this->getUser();
        if (!$loggedUser || $loggedUser !== $user) {
            throw new AccessDeniedException('Connexion au compte ciblé requise.');
        }

        $form = $this->createForm(ChangePasswordFormType::class, null, [
            'action' => $this->generateUrl('app_profile_change_password', ['id' => $user->getId()]),
            'label' => false
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $oldPlainPassword = $form->get('plainOldPassword')->getData();
            $newPlainPassword = $form->get('plainNewPassword')->getData();

            if($hasher->isPasswordValid($user, $oldPlainPassword)) {        // avant de faire quoi que ce soit on vérifie que le mot de passe correspond
                $user->setPassword($hasher->hashPassword($user, $newPlainPassword));        // on hashe le nouveau mot de passe et on le rentre en bdd

                $request->getSession()->invalidate();       // deconnexion de l'utilisateur une fois le mot de passe changé pour éviter tout problème
                $tokenStorage->setToken(null);
    
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('app_login');     // redirection vers le formulaire de connexion
            } else {
                $form->addError(new FormError('Le mot de passe ne correspond pas au mot de passe actuel.'));        // si le mot de passe ne correspond pas, on considère que le formulaire a une erreur
            }
        }

        return $this->render('user/change-password.html.twig', [
            'user' => $user,
            'form' => $form
        ]);
    }
}
