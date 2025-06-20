<?php

namespace App\Controller;

use App\Repository\ParametreSiteRepository;
use App\Entity\ParametreSite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AboutController extends AbstractController{
    /**
     * Route de la page à propos
     * @param EntityManagerInterface Interface pour gérer les données des entités
     * @param ParametreSiteRepository Interface permettant de récuperer les données de l'entité
     * @param Request Requête HTTP réçue
     * @return Template Retourne une template twig "about/about.html.twig"
     */
    #[Route('/about', name:'app_about')]
    public function about(EntityManagerInterface $em, ParametreSiteRepository $parametreSiteRepository, Request $request): Response
    {
        $parametres = $parametreSiteRepository->findAll();
        $about = $parametres[0] ?? null;

        if (!$about){
            $about = new ParametreSite(); // création d'un nouveau paramètre site si aucun existe
            $about->setAdresseSupport('');
            $about->setDescription('');
            $about->setAdresseEmail('');
            $em->persist($about);
            $em->flush();
        }

        if ($request->isMethod('POST')){
            $nouveldesc = $request->request->get('description');
            $about->setDescription($nouveldesc);
            $em->persist($about); 
            $em->flush(); // ajout des données dans la base de données
        }

        return $this->render('about/about.html.twig', [
            'about'=> $about
        ]);
    }
}