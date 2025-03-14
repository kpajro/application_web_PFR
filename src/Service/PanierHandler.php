<?php

namespace App\Service;

use App\Entity\Panier;
use App\Entity\Users;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class PanierHandler 
{
    public function __construct(private EntityManagerInterface $em, private PanierRepository $panierRepo)
    {}

    public function getActivePanier(?Users $user, Request $request) : Panier
    {
        $panier = null;
        $session = $request->getSession();
        
        
        if ($user && $user->getPanierActif() !== null) {
            $panier = $user->getPanierActif();
            $this->checkPanierLifespan($panier);
            
            if ($panier->getEtat() !== 1) {
                $panier = new Panier($user);
                $user->addPanier($panier);
                $user->setPanierActif($panier);
                
                $this->em->persist($panier);
                $this->em->persist($user);
            }
            
            $this->em->flush();
        }
        elseif ($user && !$user->getPanierActif()) {
            $panier = new Panier($user);
            $user->addPanier($panier);
            $user->setPanierActif($panier);
            
            $this->em->persist($panier);
            $this->em->persist($user);
            
            $this->em->flush();
        }
        elseif (!$user && $session->get('panier')) {
            $panierID = $session->get('panier');
            $panier = $this->panierRepo->findOneBy(['id' => $panierID]);
            $this->checkPanierLifespan($panier);

            if($panier->getEtat() !== 1) {
                $panier = new Panier($user);
                $this->em->persist($panier);

                $session->set('panier', $panier->getId());
            }

            $this->em->flush();
        }
        elseif (!$user && !$session->get('panier')) {
            $panier = new Panier($user);
            $this->em->persist($panier);
            $this->em->flush();

            $session->set('panier', $panier->getId());
        }

        return $panier;
    }

    public function checkPanierLifespan (Panier $panier) : void
    {
        $creationDate = $panier->getCreatedAt();
        $currentDate = new \DateTimeImmutable();
        $diff = date_diff($creationDate, $currentDate);
        $user = $panier->getUser();
        
        if($diff->m < 1 && $diff->d >= 7 && $user !== null) {
            $panier->setEtat(2);
            $this->em->persist($panier);

            if ($user->getPanierActif() === $panier) {
                $user->setPanierActif(null);
                $this->em->persist($user);
            }
        } 
        elseif ($diff->m < 1 && $diff->d >= 1 && $user === null) {
            $panier->setEtat(2);
            $this->em->persist($panier);
        } 
        elseif ($diff->m >= 1) {
            $panier->setEtat(3);
            $this->em->persist($panier);
            
            if ($user !== null && $user->getPanierActif() === $panier) {
                $user->setPanierActif(null);
                $this->em->persist($user);
            }
        }
    }

    public function getPanierTotalPrice(Panier $panier) : float
    {
        $totalPrice = 0;

        foreach ($panier->getPanierProduits() as $pp) {
            $totalPrice = $pp->getProduit()->getPrix() * $pp->getAmount();
        }

        return $totalPrice;
    }
}