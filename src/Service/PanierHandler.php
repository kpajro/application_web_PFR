<?php

namespace App\Service;

use App\Entity\Panier;
use App\Entity\Users;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Classe contenant des fonctions d'aide pour les paniers
 */
class PanierHandler 
{
    public function __construct(private EntityManagerInterface $em, private PanierRepository $panierRepo)
    {}

    /**
     * Fonction pour donner un panier actif à l'utilisateur, qu'il soit connecté ou non
     * @param ?Users $user L'utilisateur à qui l'on veut donner un panier, peut être null
     * @param Request $request La requête en cours, va servir pour mettre le panier en session si l'utilisateur est anonyme
     * @return Panier Renvoie un panier actif, soit nouveau soit déjà existant
     */
    public function getActivePanier(?Users $user, Request $request) : Panier
    {
        $panier = null;     // le panier qui va être renvoyé à la fin de la fonction   
        $session = $request->getSession();
        
        // si utilisateur connecté et panier actif déjà existant
        if ($user && $user->getPanierActif() !== null) {
            $panier = $user->getPanierActif();
            $this->checkPanierLifespan($panier);    // on vérifie que le panier trouvé est toujours valide
            
            // s'il n'est plus valide après la vérification, on en créé un nouveau
            if ($panier->getEtat() !== 1) {     
                $panier = new Panier($user);
                $user->addPanier($panier);
                $user->setPanierActif($panier);
                
                $this->em->persist($panier);
                $this->em->persist($user);
            }
            
            $this->em->flush();
        }
        // si utilisateur connecté et aucun panier actif trouvé
        elseif ($user && !$user->getPanierActif()) {
            // on créé un nouveau panier et on le met en panier actif
            $panier = new Panier($user);
            $user->addPanier($panier);
            $user->setPanierActif($panier);
            
            $this->em->persist($panier);
            $this->em->persist($user);
            
            $this->em->flush();
        }
        // si utilisateur non connecté et panier trouvé dans la session
        elseif (!$user && $session->get('panier')) {
            $panierID = $session->get('panier');
            $panier = $this->panierRepo->findOneBy(['id' => $panierID]); // on récupère le panier à partir de l'id stocké dans la session
            // on vérifie que le panier trouvé est toujours valide
            $this->checkPanierLifespan($panier);

            // si non valide après la verif on créé un nouveau panier et on le met dans la session
            if($panier->getEtat() !== 1) {
                $panier = new Panier($user);
                $this->em->persist($panier);

                $session->set('panier', $panier->getId());  // on met que l'id dans la session
            }

            $this->em->flush();
        }
        // si utilisateur non connecté et aucun panier trouvé dans la session
        elseif (!$user && !$session->get('panier')) {
            // on créé un nouveau panier et on le met dans la session
            $panier = new Panier($user);
            $this->em->persist($panier);
            $this->em->flush();

            $session->set('panier', $panier->getId());  // on ne met que l'id dans la session pour ne pas tout casser
        }

        return $panier;
    }

    /**
     * Fonction pour vérifier l'état d'un panier selon sa durée de vie
     * - flush à faire avec l'entity manager après utilisation de la fonction
     * @param Panier $panier Panier ciblé par la fonction
     * @return void la fonction ne retourne rien, elle modifie directement le panier
     */
    public function checkPanierLifespan (Panier $panier) : void
    {
        $creationDate = $panier->getCreatedAt();
        $currentDate = new \DateTimeImmutable();
        $diff = date_diff($creationDate, $currentDate);
        $user = $panier->getUser();
        
        // si panier d'un compte utilisateur et panier plus vieux que 7 jours -> inactif
        if($diff->m < 1 && $diff->d >= 7 && $user !== null) {
            $panier->setEtat(2);
            $this->em->persist($panier);

            if ($user->getPanierActif() === $panier) {
                $user->setPanierActif(null);
                $this->em->persist($user);
            }
        } 
        // si panier d'un utilisateur sans compte et panier plus vieux que 24h -> inactif
        elseif ($diff->m < 1 && $diff->d >= 1 && $user === null) {
            $panier->setEtat(2);
            $this->em->persist($panier);
        } 
        // si panier plus vieux qu'un mois (utilisateur avec compte ou non) -> supprimable
        elseif ($diff->m >= 1) {
            $panier->setEtat(3);
            $this->em->persist($panier);
            
            if ($user !== null && $user->getPanierActif() === $panier) {
                $user->setPanierActif(null);
                $this->em->persist($user);
            }
        }
    }

    /**
     * Fonction pour avoir le prix total d'un panier
     * @param Panier $panier Le panier ciblé
     * @return float Le prix total du panier
     */
    public function getPanierTotalPrice(Panier $panier) : float
    {
        $totalPrice = 0;

        foreach ($panier->getPanierProduits() as $pp) {
            $totalPrice += $pp->getProduit()->getPrix() * $pp->getAmount();
        }

        return $totalPrice;
    }
}