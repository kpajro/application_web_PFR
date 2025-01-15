<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/', name:'app_index', methods:'GET')]
    public function index(): Response
    {
        return $this->render('test.html.twig');
    }

    #[Route('/test', name: 'app_redirect_test')]
    public function test():Response
    {
        $names = ['leo', 'sofiane', 'klaudiusz'];
        return $this->render('test2.html.twig', [
            'names' => $names
        ]);
    }
}