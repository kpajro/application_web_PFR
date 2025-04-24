<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParametreSiteRepository;
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
        $emailContact = $paramRepository->find(1)->getAdresseSupport();
        return $this->render('admin/parametre/parametre.html.twig', [
            'emailContact' => $emailContact,
        ]);
    }

    #[Route('/edit', name: 'app_admin_parametre_edit')]
    public function mailContactEdit(Request $request, ParametreSiteRepository $paramRepository,EntityManagerInterface $em) : Response
    {
        $emailContact = $paramRepository->find(1);

        if ($request->isMethod('POST')){
            $nouvelEmail = $request->request->get('emailsupport');
            $emailContact->setAdresseSupport($nouvelEmail);
            $em->flush();
        }

        return $this->RedirectToRoute("app_admin_parametre");
    }
}
