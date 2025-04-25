<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParametreSiteRepository;
use App\Entity\ParametreSite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/parametre')]
#[IsGranted('ROLE_ADMIN')]
class BOParametreController extends AbstractController
{
    #[Route('', name: 'app_admin_parametre')]
    public function parametre(ParametreSiteRepository $paramRepository): Response
    {
        $parametres = $paramRepository->findAll();
        $emailContact = null;

        if (!empty($parametres)) {
            $emailContact = $parametres[0]->getAdresseSupport();
        }
        return $this->render('admin/parametre/parametre.html.twig', [
            'emailContact' => $emailContact,
        ]);
    }
    
    #[Route('/edit', name: 'app_admin_parametre_edit')]
    public function mailContactEdit(Request $request, ParametreSiteRepository $paramRepository, EntityManagerInterface $em) : Response
    {
        $parametres = $paramRepository->findAll();
        $parametre = $parametres[0] ?? null;

        if (!$parametre){
            $parametre = new ParametreSite();
            $parametre->setAdresseSupport('test@gmail.com');
            $parametre->setDescription('');
            $parametre->setAdresseEmail('');
            $em->persist($parametre);
            $em->flush();
        }
        
        if ($request->isMethod('POST')){
            $nouvelEmail = $request->request->get('emailsupport');
            $parametre->setAdresseSupport($nouvelEmail);
            $em->flush();
        }

        return $this->RedirectToRoute("app_admin_parametre");
    }
}
