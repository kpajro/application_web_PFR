<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/produits')]
#[IsGranted('ROLE_ADMIN')]
class BOProduitsController extends AbstractController
{
    #[Route('', name: 'app_admin_produits_list')]
    public function produits(ProduitRepository $produitRepo) : Response
    {
        $produits = $produitRepo->findAll();

        return $this->render('admin/produits/list.html.twig', [
            'produits' => $produits,
        ]);
    }
}