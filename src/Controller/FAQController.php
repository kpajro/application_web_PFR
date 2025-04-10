<?php

namespace App\Controller;


use App\Repository\FaqRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FAQController extends AbstractController{
    #[Route('/faq', name:'app_faq', methods:'GET')]
    public function faq(FaqRepository $faqRepository): Response
    {
        $categories = $faqRepository->FindAll();
        return $this->render('faq/faq.html.twig', [
            'categories'=> $categories
        ]);
    }
}