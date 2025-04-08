<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AboutController extends AbstractController{
    #[Route('/about', name:'app_about', methods:'GET')]
    public function about(): Response
    {
        return $this->render('about/about.html.twig');
    }
}