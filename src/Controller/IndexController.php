<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * Route pour la page d'accueil du site
     * @return Response Template twig 'index/index.html.twig'
     */
    #[Route('/', name:'app_index', methods:'GET')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig');
    }
}