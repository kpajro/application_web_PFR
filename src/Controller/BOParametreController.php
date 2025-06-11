<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParametreSiteRepository;
use App\Form\BOFaqFormType;
use App\Repository\FaqRepository;
use App\Entity\Faq;
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
    /**
     * Route du backoffice de la page paramètres du site
     */
    #[Route('', name: 'app_admin_parametre')]
    public function parametre(ParametreSiteRepository $paramRepository, FaqRepository $faqRepository, EntityManagerInterface $em): Response
    {
        $parametres = $paramRepository->findAll();
        $faqs = $faqRepository->findAll();

        $emailContact = null;

        if (empty($faqs)){
            $faq = new Faq(); // si aucune FAQ existe créer une nouvelle par défaut
            $faq->setCategorie(1);
            $faq->setQuestions(["Template question"]);
            $faq->setReponses(["Template answer"]);
            $em->persist($faq);
            $em->flush();
        }
        
        if (!empty($parametres)) {
            $emailContact = $parametres[0]->getAdresseSupport(); // si aucune adresse support existe créer une nouvelle
        }
        return $this->render('admin/parametre/parametre.html.twig', [
            'emailContact' => $emailContact,
            'faqs' => $faqs
        ]);
    }
    /**
     * Route du backoffice permettant de faire une modification des paramètres du site
     */
    #[Route('/edit', name: 'app_admin_parametre_edit')]
    public function mailContactEdit(Request $request, ParametreSiteRepository $paramRepository, EntityManagerInterface $em) : Response
    {
        $parametres = $paramRepository->findAll();
        $parametre = $parametres[0] ?? null;
        
        if (!$parametre){
            $parametre = new ParametreSite(); // si aucun paramètre existe créer un nouvel object paramètre
            $parametre->setAdresseSupport('test@gmail.com');
            $parametre->setDescription('');
            $parametre->setAdresseEmail('');
            $em->persist($parametre);
            $em->flush();
        }
        
        if ($request->isMethod('POST')){ 
            $nouvelEmail = $request->request->get('emailsupport'); // modification des données si la requete est une POST
            $parametre->setAdresseSupport($nouvelEmail);
            $em->flush();
        }

        return $this->RedirectToRoute("app_admin_parametre"); // redirection vers la route principale
    }
    /**
     * Route du backoffice permettant de modifier d'une FAQ 
     */
    #[Route('/{id}/faq', name: 'app_admin_faq_edit')]
    public function faqEdit(Request $request, FaqRepository $faqRepository, EntityManagerInterface $em, Faq $faq): Response 
    {
        $form = $this->createForm(
            BOFaqFormType::class,  // création d'un formulaire de modification d'une FAQ dans une modale 
            $faq,
            ['action' => $this->generateUrl('app_admin_faq_edit', ['id' => $faq->getId()])] 
        );
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($faq);
            $em->flush();

            return $this->redirectToRoute('app_admin_parametre');
        }
        return $this->render('elements/form_backoffice.html.twig', [
            'title' => 'Modification des questions-réponses ' . $faq->getCategorie(),
            'btnAction' => 'Modifier',
            'deletable' => false,
            'form' => $form
        ]);
    }

}
