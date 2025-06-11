<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller du dashboard du BackOffice
 * - Connexion en tant qu'administrateur necessaire
 */
#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    /**
     * Route du dashboard du BackOffice : '/admin/' ('app_admin_home')
     */
    #[Route('', name:'app_admin_home')]
    public function index() : Response 
    {
        return $this->render('admin/index.html.twig', [

        ]);
    }
}