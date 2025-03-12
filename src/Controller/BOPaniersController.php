<?php

namespace App\Controller;

use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/admin/paniers')]
#[IsGranted('ROLE_ADMIN')]
class BOPaniersController extends AbstractController
{
    #[Route('/', name: 'app_admin_paniers_list')]
    public function listPaniers (PanierRepository $panierRepository) : Response
    {
        $paniers = $panierRepository->findAll();

        return $this->render('/admin/paniers/list.hmtl.twig', [
            'paniers' => $paniers
        ]);
    }
}