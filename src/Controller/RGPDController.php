<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RGPDController extends AbstractController
{
    #[Route('/rgpd/confidentiality', name: 'app_rgpd_confidentiality')]
    public function index(): Response
    {
        return $this->render('rgpd/confidentialite.html.twig');
    }
}
