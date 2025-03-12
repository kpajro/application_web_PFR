<?php

namespace App\Service;

use App\Entity\Panier;
use App\Entity\Users;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;

class PanierHandler 
{
    public function __construct(private EntityManagerInterface $em, private PanierRepository $panierRepo)
    {}

    public function createNewPanier(?Users $user) : Panier
    {
        $panier = new Panier();
        $panier->setEtat(1);
        $panier->setCreatedAt(new \DateTimeImmutable('now'));
        $panier->setUser($user !== null ? $user : null);

        return $panier;
    }
}