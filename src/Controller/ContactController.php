<?php

namespace App\Controller;
use App\Form\ContactFormType;
use App\Repository\ParametreSiteRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    /**
     * Route de la page Contact permettant d'envoyer un message au support
     *
     * @param Request $request Requête HTTP reçue (GET ou POST)
     * @param MailerInterface $mailer Service pour l'envoi d'e-mails
     * @param ParametreSiteRepository $paramRepository Interface permettant de récupérer les paramètres du site (dont l'adresse support)
     * @return Response Retourne le formulaire de contact ou redirige après envoi
     */
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer, ParametreSiteRepository $paramRepository): Response
    {
        $form = $this->createForm(ContactFormType::class); // création d'un formulaire de contact
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData(); // récupération des données du formulaire

            $param = $paramRepository->find(1); // récupération du paramètre contenant l'adresse support
            $supportEmail = $param?->getAdresseSupport();

            $email = (new TemplatedEmail()) // création d'une template d'email
                ->from(new Address($data['email']))
                ->to($supportEmail)
                ->subject('NOMICI - Confirmation de votre demande')
                ->htmlTemplate('emails/contact.html.twig')
                ->context([
                    'adresse_mail' => $data['email'],
                    'message' => $data['message'],
                ]);

            $mailer->send($email); // envoi de l'e-mail

            return $this->redirectToRoute('app_contact'); // redirection après envoi
        }

        return $this->render('contact/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
