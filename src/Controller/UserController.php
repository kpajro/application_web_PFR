<?php

namespace App\Controller;

use App\Entity\Panier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;
use App\Form\ChangePasswordFormType;
use Dompdf\Dompdf;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormError;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

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


    #[Route('/profile/{id}/supprimer-mon-profil', name:'app_profile_delete_account')]
    public function deleteAccount(Users $user, EntityManagerInterface $em, Request $request, TokenStorageInterface $tokenStorage, UserPasswordHasherInterface $hasher): Response
    {
        $loggedUser = $this->getUser();

        if (!$loggedUser || $user !== $loggedUser) {
            throw new AccessDeniedException('Connexion au compte ciblé requise');
        }

        $formBuilder = $this->createFormBuilder(null, [
            'action' => $this->generateUrl('app_profile_delete_account', ['id' => $user->getId()])

        ])
            ->add('plainPassword', PasswordType::class, [
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

        if ($form->isSubmitted() && $form->isValid() && $hasher->isPasswordValid($user, $form->get('plainPassword')->getData())) {
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

    #[Route('/profile/restore-panier/{id}', name: 'app_profile_restore_panier')]
    public function restorePanier (Panier $panier, EntityManagerInterface $em) : Response 
    {
        /** @var Users $user */ 
        $user = $this->getUser();
        if ($user !== $panier->getUser()) {
            throw new AccessDeniedException('Le propriétaire du panier ne correspond pas à l\'utilisateur connecté.');
        }

        $currentPanier = $user->getPanierActif();
        $panierProduits = $panier->getPanierProduits();

        $currentPanier->setEtat(2);
        $newPanier = new Panier($user);
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

    #[Route('/profile/{id}/change-password', name: 'app_profile_change_password')]
    public function changePassword(Users $user, EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $hasher, TokenStorageInterface $tokenStorage) : Response
    {
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

            if($hasher->isPasswordValid($user, $oldPlainPassword)) {
                $user->setPassword($hasher->hashPassword($user, $newPlainPassword));

                $request->getSession()->invalidate();
                $tokenStorage->setToken(null);
    
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('app_login');
            } else {
                $form->addError(new FormError('Le mot de passe ne correspond pas au mot de passe actuel.'));
            }
        }

        return $this->render('user/change-password.html.twig', [
            'user' => $user,
            'form' => $form
        ]);
    }
}
