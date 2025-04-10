<?php

namespace App\Controller;

use App\Repository\ParametreSiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AboutController extends AbstractController{
    #[Route('/about', name:'app_about')]
    public function about(EntityManagerInterface $em, ParametreSiteRepository $parametreSiteRepository, Request $request): Response
    {
        $about = $parametreSiteRepository->find(1);

        if ($request->isMethod('POST')){
            $about->setDescription($request->request->get('description'));
            $em->persist($about);
            $em->flush();
        }

        return $this->render('about/about.html.twig', [
            'about'=> $about
        ]);
    }
}